<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookOutEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookOutboundController extends Controller
{
    /**
     * Runner endpoint: envia webhooks pendentes para o destino configurado.
     * Deve ser chamado via cron ou scheduler com a chave correta.
     */
    public function run(Request $request): JsonResponse
    {
        // Valida chave de acesso ao runner
        if ($request->query('key') !== config('eletrodim.webhook_out_runner_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $outUrl = config('eletrodim.webhook_out_url');

        $events = WebhookOutEvent::where('status', 'pending')
            ->orderBy('created_at')
            ->take(50)
            ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($events as $event) {
            try {
                $payload = is_string($event->payload)
                    ? json_decode($event->payload, true)
                    : $event->payload;

                $response = Http::timeout(10)->post($outUrl, [
                    'event'   => $event->event,
                    'payload' => $payload,
                ]);

                if ($response->successful()) {
                    $event->update([
                        'status'  => 'sent',
                        'sent_at' => now(),
                    ]);
                    $sent++;
                } else {
                    $attempts = $event->attempts + 1;
                    $updates  = [
                        'attempts'   => $attempts,
                        'last_error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                    ];

                    if ($attempts >= 3) {
                        $updates['status'] = 'failed';
                    }

                    $event->update($updates);
                    $failed++;
                }
            } catch (\Throwable $e) {
                $attempts = $event->attempts + 1;
                $updates  = [
                    'attempts'   => $attempts,
                    'last_error' => $e->getMessage(),
                ];

                if ($attempts >= 3) {
                    $updates['status'] = 'failed';
                }

                $event->update($updates);
                $failed++;
            }
        }

        return response()->json([
            'processed' => $events->count(),
            'sent'      => $sent,
            'failed'    => $failed,
        ]);
    }
}

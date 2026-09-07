<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookInEvent;
use App\Services\WebhookInboundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookInboundController extends Controller
{
    public function __construct(protected WebhookInboundService $service) {}

    /**
     * Recebe e processa webhooks de acesso de sistemas externos.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Valida token de autenticação
        $token = $request->header('X-EletroDIM-Token');
        if ($token !== config('eletrodim.webhook_in_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload   = $request->all();
        $eventType = $payload['event'] ?? $payload['event_type'] ?? null;

        // 2. Registra payload bruto como pendente
        $event = WebhookInEvent::create([
            'event_type'     => $eventType,
            'payload_json'   => json_encode($payload),
            'process_status' => 'pending',
            'received_at'    => now(),
        ]);

        // 3. Verifica idempotência: evento já processado anteriormente (por event_type + payload hash)
        $payloadHash = md5($event->payload_json);
        $existing = WebhookInEvent::where('event_type', $eventType)
            ->where('process_status', 'processed')
            ->where('id', '!=', $event->id)
            ->whereRaw('MD5(payload_json) = ?', [$payloadHash])
            ->first();

        if ($existing) {
            // Remove o registro duplicado recém criado
            $event->delete();

            return response()->json([
                'message'    => 'Event already processed',
                'event_type' => $eventType,
            ], 200);
        }

        // 4. Processa o evento via service
        $result = $this->service->process($event);

        return response()->json([
            'message' => $result['success'] ? 'Event processed successfully' : 'Event processing failed',
            'result'  => $result,
        ], $result['success'] ? 200 : 422);
    }
}

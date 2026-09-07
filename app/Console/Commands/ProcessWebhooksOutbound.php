<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WebhookOutEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWebhooksOutbound extends Command
{
    protected $signature = 'webhooks:process-outbound {--limit=50}';
    protected $description = 'Processa e envia webhooks de saída pendentes';

    public function handle(): int
    {
        $limit  = (int) $this->option('limit');
        $events = WebhookOutEvent::where('status', 'pending')->take($limit)->get();

        if ($events->isEmpty()) {
            $this->info('Nenhum webhook pendente.');
            return Command::SUCCESS;
        }

        $outUrl = config('eletrodim.webhook_out_url');
        if (empty($outUrl)) {
            $this->warn('ELETRODIM_WEBHOOK_OUT_URL não configurado.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($events as $event) {
            try {
                $response = Http::timeout(10)->post($outUrl, json_decode($event->payload_json, true));

                if ($response->successful()) {
                    $event->update(['status' => 'sent', 'sent_at' => now()]);
                    $sent++;
                } else {
                    $this->handleFailure($event, 'HTTP ' . $response->status());
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->handleFailure($event, $e->getMessage());
                $failed++;
                Log::error('Webhook outbound error: ' . $e->getMessage(), ['event_id' => $event->id]);
            }
        }

        $this->info("Enviados: {$sent} | Falhos: {$failed}");
        return Command::SUCCESS;
    }

    private function handleFailure(WebhookOutEvent $event, string $error): void
    {
        $attempts = $event->attempts + 1;
        $event->update([
            'attempts'   => $attempts,
            'last_error' => $error,
            'status'     => $attempts >= 3 ? 'failed' : 'pending',
        ]);
    }
}

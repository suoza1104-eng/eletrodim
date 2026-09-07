<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\WebhookInEvent;
use App\Models\WebhookOutEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookInboundService
{
    // Processa um WebhookInEvent e retorna array com resultado
    public function process(WebhookInEvent $event): array
    {
        try {
            $payload = json_decode($event->payload_json, true, 512, JSON_THROW_ON_ERROR);
            $eventType = $event->event_type ?? ($payload['event'] ?? '');

            $result = match(true) {
                str_starts_with($eventType, 'student.create') || $eventType === 'purchase.approved' => $this->handleStudentCreate($payload),
                $eventType === 'student.upsert' => $this->handleStudentUpsert($payload),
                $eventType === 'access.activate' => $this->handleAccessActivate($payload),
                $eventType === 'access.block' => $this->handleAccessBlock($payload),
                $eventType === 'access.cancel' => $this->handleAccessCancel($payload),
                $eventType === 'access.extend' => $this->handleAccessExtend($payload),
                $eventType === 'access.expire' => $this->handleAccessExpire($payload),
                default => ['success' => false, 'message' => "Evento desconhecido: {$eventType}"],
            };

            $event->update([
                'process_status' => $result['success'] ? 'processed' : 'failed',
                'process_message' => $result['message'] ?? null,
                'processed_at' => now(),
            ]);

            return $result;

        } catch (\Throwable $e) {
            Log::error('WebhookInboundService error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $event->update([
                'process_status' => 'failed',
                'process_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Cria novo aluno (vindo de compra aprovada)
    // Payload esperado: email, name, phone?, password?, access_expires_at?
    private function handleStudentCreate(array $payload): array
    {
        $email = strtolower(trim($payload['email'] ?? ''));
        if (!$email) return ['success' => false, 'message' => 'E-mail ausente no payload.'];

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            // Se já existe, apenas ativa e renova acesso
            return $this->handleStudentUpsert($payload);
        }

        $password = $payload['password'] ?? Str::random(12);
        $expiresAt = isset($payload['access_expires_at'])
            ? \Carbon\Carbon::parse($payload['access_expires_at'])
            : now()->addYear();

        $user = User::create([
            'role' => 'student',
            'name' => $payload['name'] ?? 'Aluno',
            'email' => $email,
            'phone' => $payload['phone'] ?? null,
            'password' => Hash::make($password),
            'status' => 'active',
            'access_starts_at' => now(),
            'access_expires_at' => $expiresAt,
        ]);

        $this->dispatchOutbound('student.created', $user->id, [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'password_plain' => $password,
            'access_expires_at' => $expiresAt->toISOString(),
        ]);

        return ['success' => true, 'message' => "Aluno {$email} criado com sucesso.", 'user_id' => $user->id];
    }

    // Cria ou atualiza aluno (upsert)
    private function handleStudentUpsert(array $payload): array
    {
        $email = strtolower(trim($payload['email'] ?? ''));
        if (!$email) return ['success' => false, 'message' => 'E-mail ausente no payload.'];

        $expiresAt = isset($payload['access_expires_at'])
            ? \Carbon\Carbon::parse($payload['access_expires_at'])
            : now()->addYear();

        $updateData = [
            'status' => 'active',
            'access_expires_at' => $expiresAt,
        ];
        if (!empty($payload['name'])) $updateData['name'] = $payload['name'];
        if (!empty($payload['phone'])) $updateData['phone'] = $payload['phone'];

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->update($updateData);
            return ['success' => true, 'message' => "Aluno {$email} atualizado.", 'user_id' => $user->id];
        }

        return $this->handleStudentCreate($payload);
    }

    private function handleAccessActivate(array $payload): array
    {
        $user = $this->findUser($payload);
        if (!$user) return ['success' => false, 'message' => 'Usuário não encontrado.'];

        $expiresAt = isset($payload['access_expires_at'])
            ? \Carbon\Carbon::parse($payload['access_expires_at'])
            : ($user->access_expires_at ?? now()->addYear());

        $user->update(['status' => 'active', 'access_expires_at' => $expiresAt, 'access_starts_at' => now()]);
        return ['success' => true, 'message' => "Acesso de {$user->email} ativado."];
    }

    private function handleAccessBlock(array $payload): array
    {
        $user = $this->findUser($payload);
        if (!$user) return ['success' => false, 'message' => 'Usuário não encontrado.'];
        $user->update(['status' => 'blocked']);
        return ['success' => true, 'message' => "Acesso de {$user->email} bloqueado."];
    }

    private function handleAccessCancel(array $payload): array
    {
        $user = $this->findUser($payload);
        if (!$user) return ['success' => false, 'message' => 'Usuário não encontrado.'];
        $user->update(['status' => 'cancelled']);
        return ['success' => true, 'message' => "Acesso de {$user->email} cancelado."];
    }

    private function handleAccessExtend(array $payload): array
    {
        $user = $this->findUser($payload);
        if (!$user) return ['success' => false, 'message' => 'Usuário não encontrado.'];

        $days = (int)($payload['days'] ?? 365);
        $baseDate = $user->access_expires_at && $user->access_expires_at->isFuture()
            ? $user->access_expires_at
            : now();
        $newExpiry = $baseDate->addDays($days);

        $user->update(['status' => 'active', 'access_expires_at' => $newExpiry]);
        return ['success' => true, 'message' => "Acesso de {$user->email} estendido até {$newExpiry->format('d/m/Y')}."];
    }

    private function handleAccessExpire(array $payload): array
    {
        $user = $this->findUser($payload);
        if (!$user) return ['success' => false, 'message' => 'Usuário não encontrado.'];
        $user->update(['status' => 'expired']);
        return ['success' => true, 'message' => "Acesso de {$user->email} expirado."];
    }

    private function findUser(array $payload): ?User
    {
        if (!empty($payload['email'])) {
            return User::where('email', strtolower(trim($payload['email'])))->first();
        }
        if (!empty($payload['user_id'])) {
            return User::find($payload['user_id']);
        }
        return null;
    }

    private function dispatchOutbound(string $eventType, ?int $userId, array $data): void
    {
        WebhookOutEvent::create([
            'event_type' => $eventType,
            'user_id' => $userId,
            'project_id' => null,
            'payload_json' => json_encode($data),
            'status' => 'pending',
            'attempts' => 0,
        ]);
    }
}

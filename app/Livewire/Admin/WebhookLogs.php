<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WebhookOutEvent;
use App\Models\WebhookInEvent;

class WebhookLogs extends Component
{
    use WithPagination;

    public string $tab = 'out'; // 'out' ou 'in'

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage('outPage');
        $this->resetPage('inPage');
    }

    public function requeue(int $eventId): void
    {
        WebhookOutEvent::findOrFail($eventId)->update([
            'status'     => 'pending',
            'attempts'   => 0,
            'last_error' => null,
        ]);
        session()->flash('success', 'Webhook reenfileirado.');
    }

    public function render()
    {
        $outEvents = WebhookOutEvent::with(['user'])
            ->latest()
            ->paginate(20, pageName: 'outPage');

        $inEvents = WebhookInEvent::latest('received_at')
            ->paginate(20, pageName: 'inPage');

        return view('livewire.admin.webhook-logs', compact('outEvents', 'inEvents'));
    }
}

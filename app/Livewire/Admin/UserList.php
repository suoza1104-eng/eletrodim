<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showForm = false;
    public ?int $editingUserId = null;

    // Campos do formulário
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $status = 'active';
    public string $accessExpiresAt = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingUserId = null;
    }

    public function openEditForm(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->status = $user->status;
        $this->accessExpiresAt = $user->access_expires_at?->format('Y-m-d') ?? '';
        $this->password = '';
        $this->showForm = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'name'            => 'required|min:2|max:150',
            'email'           => 'required|email|unique:users,email' . ($this->editingUserId ? ",{$this->editingUserId}" : ''),
            'phone'           => 'nullable|max:30',
            'status'          => 'required|in:active,blocked,cancelled,expired',
            'accessExpiresAt' => 'nullable|date',
        ];

        if (! $this->editingUserId) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        $data = [
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone ?: null,
            'status'            => $this->status,
            'access_expires_at' => $this->accessExpiresAt ?: null,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->editingUserId) {
            User::findOrFail($this->editingUserId)->update($data);
            $msg = 'Aluno atualizado com sucesso.';
        } else {
            $data['role'] = 'student';
            User::create($data);
            $msg = 'Aluno criado com sucesso.';
        }

        $this->resetForm();
        $this->showForm = false;
        session()->flash('success', $msg);
    }

    public function quickAction(int $userId, string $action): void
    {
        $user = User::where('role', 'student')->findOrFail($userId);
        match ($action) {
            'activate' => $user->update(['status' => 'active']),
            'block'    => $user->update(['status' => 'blocked']),
            'cancel'   => $user->update(['status' => 'cancelled']),
            default    => null,
        };
        session()->flash('success', 'Status do aluno atualizado.');
    }

    private function resetForm(): void
    {
        $this->name = $this->email = $this->phone = $this->password = $this->accessExpiresAt = '';
        $this->status = 'active';
        $this->editingUserId = null;
    }

    public function render()
    {
        $users = User::where('role', 'student')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->withCount('projects')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.user-list', compact('users'));
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class ProjectList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $confirmingDelete = false;
    public ?int $deleteProjectId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $projectId): void
    {
        $this->deleteProjectId = $projectId;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->deleteProjectId = null;
    }

    public function deleteProject(): void
    {
        $project = Project::where('id', $this->deleteProjectId)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();
        $project->delete();
        $this->confirmingDelete = false;
        $this->deleteProjectId = null;
        session()->flash('success', 'Projeto excluído com sucesso.');
    }

    public function duplicateProject(int $projectId): void
    {
        $original = Project::where('id', $projectId)
                           ->where('user_id', Auth::id())
                           ->firstOrFail();
        $copy = $original->replicate();
        $copy->name = $original->name . ' (cópia)';
        $copy->status = 'draft';
        $copy->progress_step = 1;
        $copy->progress_percent = 0;
        $copy->share_token = null;
        $copy->completed_at = null;
        $copy->save();
        session()->flash('success', 'Projeto duplicado com sucesso.');
    }

    public function render()
    {
        $projects = Auth::user()->projects()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('client_name', 'like', '%' . $this->search . '%');
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.student.project-list', compact('projects'));
    }
}

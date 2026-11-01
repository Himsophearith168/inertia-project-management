<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Projects/Index', [
            'projects' => Project::query()->latest()->paginate(8)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Project::create($this->validated($request));
        return to_route('projects.index')->with('success', 'Project created successfully.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request));
        return to_route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        return to_route('projects.index')->with('success', 'Project deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'client' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:planning,in_progress,completed,on_hold'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}

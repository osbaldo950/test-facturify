<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateThreadAction;
use App\Enums\ThreadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Thread\IndexThreadRequest;
use App\Http\Requests\Api\Thread\StoreThreadRequest;
use App\Http\Resources\Api\ThreadResource;
use App\Models\Thread;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ThreadController extends Controller
{
    use ApiResponse;

    public function index(IndexThreadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);

        $threads = Thread::query()
            ->with('creator')
            ->withCount('messages')
            ->search($validated['search'] ?? null)
            ->when(
                isset($validated['status']),
                fn ($query) => $query->where('status', ThreadStatus::from($validated['status']))
            )
            ->latest('last_message_at')
            ->latest('id')
            ->paginate($perPage);

        return $this->successResponse([
            'threads' => ThreadResource::collection($threads)->resolve(),
            'meta' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
        ], 'Conversaciones obtenidas correctamente.');
    }

    public function store(StoreThreadRequest $request, CreateThreadAction $createThread): JsonResponse
    {
        $thread = $createThread->handle($request->user('api'), $request->validated());

        return $this->successResponse(
            new ThreadResource($thread),
            'Conversación creada exitosamente.',
            201
        );
    }

    public function show(Thread $thread): JsonResponse
    {
        $thread->load([
            'creator',
            'messages' => fn ($query) => $query->with('user')->oldest(),
        ]);

        return $this->successResponse(
            new ThreadResource($thread),
            'Detalle de la conversación obtenido correctamente.'
        );
    }
}

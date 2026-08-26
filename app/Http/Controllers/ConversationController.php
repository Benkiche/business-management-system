<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Customer;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all conversations.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        $query = Conversation::with(['creator', 'participants', 'latestMessages'])
            ->forUser($user)
            ->latest('last_message_at');

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', '>=', $request->priority);
        }

        $conversations = $query->paginate(20);

        $selectedConversationQuery = Conversation::with([
            'creator',
            'participants',
            'messages.sender',
            'messages.reads',
            'customer',
        ])->forUser($user);

        $selectedConversation = $request->filled('conversation')
            ? $selectedConversationQuery->whereKey($request->integer('conversation'))->first()
            : $selectedConversationQuery->latest('last_message_at')->first();

        $stats = [
            'total' => Conversation::forUser($user)->count(),
            'open' => Conversation::forUser($user)->open()->count(),
            'unread' => Conversation::forUser($user)->get()
                ->sum(fn($c) => $c->unreadCount($user)),
        ];

        AuditService::log('viewed', 'Conversation', null, 'Viewed conversations list');

        return view('conversations.index', compact('conversations', 'stats', 'selectedConversation'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $users = User::where('status', 'active')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        $customers = Customer::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('conversations.create', compact('users', 'customers'));
    }

    /**
     * Store conversation.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Check permission
        if (!$user->hasPermission('conversation.create')) {
            return back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:customer_support,internal,sales,billing',
            'priority' => 'nullable|integer|in:1,2,3',
            'customer_id' => 'nullable|exists:customers,id',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        try {
            $conversation = Conversation::create([
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'priority' => $validated['priority'] ?? 1,
                'customer_id' => $validated['customer_id'] ?? null,
                'created_by' => $user->id,
                'status' => 'open',
            ]);

            // Add creator as participant
            $conversation->addParticipant($user, 'creator');

            // Add selected participants
            foreach ($validated['participants'] as $participantId) {
                $participant = User::findOrFail($participantId);
                $conversation->addParticipant($participant, 'participant');
            }

            // Add system message
            $conversation->messages()->create([
                'body' => "{$user->name} started this conversation",
                'type' => 'system',
            ]);

            AuditService::log(
                'created',
                'Conversation',
                $conversation->id,
                "Created conversation: {$conversation->subject}"
            );

            return redirect()
                ->route('conversations.show', $conversation)
                ->with('success', 'Conversation created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create conversation: ' . $e->getMessage());
        }
    }

    /**
     * Show conversation.
     */
    public function show(Conversation $conversation): View
    {
        // Check authorization
        if (!$conversation->canParticipate(auth()->user())) {
            abort(403, 'Unauthorized');
        }

        $conversation->load(['creator', 'participants', 'messages.sender', 'messages.reads', 'customer']);

        // Mark conversation as read
        $conversation->markAsRead(auth()->user());

        // Mark messages as read
        foreach ($conversation->messages as $message) {
            $message->markAsRead(auth()->user());
        }

        AuditService::log(
            'viewed',
            'Conversation',
            $conversation->id,
            "Opened conversation: {$conversation->subject}"
        );

        return view('conversations.show', compact('conversation'));
    }

    /**
     * Close conversation.
     */
    public function close(Conversation $conversation): RedirectResponse
    {
        // Check authorization
        if (!auth()->user()->isAdmin() && !$conversation->creator->is(auth()->user())) {
            abort(403);
        }

        $conversation->update(['status' => 'closed']);

        // Add system message
        $conversation->messages()->create([
            'body' => auth()->user()->name . ' closed this conversation',
            'type' => 'system',
        ]);

        AuditService::log(
            'updated',
            'Conversation',
            $conversation->id,
            'Closed conversation'
        );

        return back()->with('success', 'Conversation closed');
    }

    /**
     * Reopen conversation.
     */
    public function reopen(Conversation $conversation): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $conversation->update(['status' => 'open']);

        $conversation->messages()->create([
            'body' => auth()->user()->name . ' reopened this conversation',
            'type' => 'system',
        ]);

        AuditService::log(
            'updated',
            'Conversation',
            $conversation->id,
            'Reopened conversation'
        );

        return back()->with('success', 'Conversation reopened');
    }

    /**
     * Add participant.
     */
    public function addParticipant(Request $request, Conversation $conversation): JsonResponse
    {
        if (!auth()->user()->isAdmin() && !$conversation->creator->is(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'User already in conversation'], 400);
        }

        $conversation->addParticipant($user);

        $conversation->messages()->create([
            'body' => auth()->user()->name . ' added ' . $user->name . ' to the conversation',
            'type' => 'system',
        ]);

        return response()->json(['success' => true, 'message' => 'Participant added']);
    }

    /**
     * Remove participant.
     */
    public function removeParticipant(Conversation $conversation, User $participant): RedirectResponse
    {
        // Only creator or admin can remove
        if (!auth()->user()->isAdmin() && !$conversation->creator->is(auth()->user())) {
            abort(403);
        }

        // Can't remove creator
        if ($participant->is($conversation->creator)) {
            return back()->with('error', 'Cannot remove conversation creator');
        }

        $conversation->removeParticipant($participant);

        $conversation->messages()->create([
            'body' => auth()->user()->name . ' removed ' . $participant->name,
            'type' => 'system',
        ]);

        AuditService::log(
            'updated',
            'Conversation',
            $conversation->id,
            "Removed participant: {$participant->name}"
        );

        return back()->with('success', 'Participant removed');
    }

    /**
     * Delete conversation.
     */
    public function destroy(Conversation $conversation): RedirectResponse
    {
        if (!$conversation->canDelete(auth()->user())) {
            abort(403);
        }

        $subject = $conversation->subject;
        $conversation->delete();

        AuditService::log(
            'deleted',
            'Conversation',
            $conversation->id,
            "Deleted conversation: {$subject}"
        );

        return redirect()
            ->route('conversations.index')
            ->with('success', 'Conversation deleted');
    }

    /**
     * Get unread count (AJAX).
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = auth()->user()->getUnreadConversationCount();
        $totalUnread = Conversation::forUser(auth()->user())->get()
            ->sum(fn($c) => $c->unreadCount(auth()->user()));

        return response()->json([
            'unread_conversations' => $count,
            'total_unread_messages' => $totalUnread,
        ]);
    }
}
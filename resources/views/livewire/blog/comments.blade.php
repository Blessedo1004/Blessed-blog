<div class="mt-12 border-t border-gray-200 pt-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">
        Comments ({{ $comments->count() + $comments->sum(fn($c) => $c->replies->count()) }})
    </h2>

    <!-- Success Messages -->
    @if (session('comment-success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-green-800">{{ session('comment-success') }}</p>
        </div>
    @endif

    @if (session('delete-success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-green-800">{{ session('delete-success') }}</p>
        </div>
    @endif

    @if (session('delete-error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-red-800">{{ session('delete-error') }}</p>
        </div>
    @endif

    <!-- New Comment Form -->
    @auth
        <div class="mb-8 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave a comment</h3>
            <form wire:submit="postComment">
                <textarea 
                    wire:model="newComment"
                    rows="4"
                    placeholder="Share your thoughts..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
                @error('newComment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-4 flex justify-end">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Post Comment
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="mb-8 bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-600 mb-4">You must be logged in to comment.</p>
            <a 
                href="{{ route('login') }}" 
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
            >
                Login to Comment
            </a>
        </div>
    @endauth

    <!-- Comments List -->
    <div class="space-y-6">
        @forelse($comments as $comment)
            <div wire:key="comment-{{ $comment->id }}" class="bg-white rounded-lg border border-gray-200 p-6">
                <!-- Comment Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <img 
                            src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=4f46e5&color=fff" 
                            alt="{{ $comment->user->name }}" 
                            class="w-10 h-10 rounded-full mr-3"
                        >
                        <div>
                            <p class="font-medium text-gray-900">{{ $comment->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Comment Content -->
                <div class="text-gray-700 mb-4">
                    @php 
                        $commentLen = mb_strlen($comment->content); 
                        $isExpanded = in_array($comment->id, $expandedComments);
                    @endphp

                    @if($commentLen > 20 && !$isExpanded)
                        {{ Str::limit($comment->content, 20) }}
                        <button wire:click="toggleExpand({{ $comment->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            ...show more
                        </button>
                    @else
                        {{ $comment->content }}
                        @if($commentLen > 20 && $isExpanded)
                            <button wire:click="toggleExpand({{ $comment->id }})" class="text-xs text-gray-500 hover:text-gray-700 ml-2 italic">
                                (show less)
                            </button>
                        @endif
                    @endif
                </div>

                <!-- Comment Actions -->
                <div class="flex items-center gap-4">
                    @auth
                        @if($replyingTo === $comment->id)
                            <button 
                                wire:click="cancelReply"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </button>
                        @else
                            <button 
                                wire:click="startReply({{ $comment->id }})"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                            >
                                Reply
                            </button>
                        @endif

                        @if($comment->user_id === Auth::id())
                            <button 
                                wire:click="deleteComment({{ $comment->id }})"
                                wire:confirm="Are you sure you want to delete this comment?"
                                class="text-red-600 hover:text-red-900 text-sm"
                            >
                                Delete
                            </button>  
                        @endif
                    @endauth
                </div>

                <!-- Reply Form -->
                @if($replyingTo === $comment->id)
                    <div class="mt-4 bg-gray-50 rounded-lg p-4" wire:transition>
                        <form wire:submit="postReply({{ $comment->id }})">
                            <textarea 
                                wire:model="replyContent"
                                rows="3"
                                placeholder="Write your reply..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            @error('replyContent')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <div class="mt-3 flex justify-end gap-2">
                                <button 
                                    type="button"
                                    wire:click="cancelReply"
                                    class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                                >
                                    Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Replies Section -->
                @if($comment->replies->count() > 0)
                    <div class="mt-4">
                        @if($repliesFor !== $comment->id)
                            <div class="flex items-center gap-2 cursor-pointer" wire:click="showReplies({{ $comment->id }})">
                                <div class="text-sm text-gray-500">
                                    {{ $comment->replies->count() }} {{ Str::plural('Reply', $comment->replies->count()) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <div wire:loading wire:target="showReplies({{ $comment->id }})">
                                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2 cursor-pointer" wire:click="hideReplies">
                                <div class="text-sm text-gray-500">Hide Replies</div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <div wire:loading wire:target="hideReplies">
                                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Actual Replies List -->
                            <div class="mt-6 ml-8 space-y-4 border-l-2 border-gray-200 pl-6" wire:transition>
                                @foreach($comment->replies->sortByDesc('id') as $reply)
                                    <div wire:key="reply-{{ $reply->id }}" class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center">
                                                <img 
                                                    src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&background=6366f1&color=fff" 
                                                    alt="{{ $reply->user->name }}" 
                                                    class="w-8 h-8 rounded-full mr-3"
                                                >
                                                <div>
                                                    <p class="font-medium text-gray-900 text-sm">{{ $reply->user->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>

                                            @if($reply->user_id === Auth::id())
                                                <button 
                                                    wire:click="deleteComment({{ $reply->id }})"
                                                    wire:confirm="Are you sure you want to delete this reply?"
                                                    class="text-red-600 hover:text-red-900 text-xs"
                                                >
                                                    Delete
                                                </button>  
                                            @endif
                                        </div>
                                        <div class="text-gray-700 text-sm">
                                            @php 
                                                $replyLen = mb_strlen($reply->content); 
                                                $isReplyExpanded = in_array($reply->id, $expandedComments);
                                            @endphp

                                            @if($replyLen > 20 && !$isReplyExpanded)
                                                {{ Str::limit($reply->content, 20) }}
                                                <button wire:click="toggleExpand({{ $reply->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                                    ...show more
                                                </button>
                                            @else
                                                {{ $reply->content }}
                                                @if($replyLen > 20 && $isReplyExpanded)
                                                    <button wire:click="toggleExpand({{ $reply->id }})" class="text-xs text-gray-500 hover:text-gray-700 ml-2 italic">
                                                        (show less)
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12">
                <p class="text-gray-500">No comments yet. Be the first to share your thoughts!</p>
            </div>
        @endforelse
    </div>
</div>

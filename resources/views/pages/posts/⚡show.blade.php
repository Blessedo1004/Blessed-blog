<?php

use Livewire\Component;
use App\Models\Post;
use Livewire\Attributes\Layout;
use App\Models\PostView;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.public')] class extends Component
{
    public Post $post;
    public $perPage = 3;

    public function mount($slug)
    {
        $this->post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['user','categories','tags'])
            ->firstOrFail();
        
        $this->trackView();
    }

    protected function trackView()
    {
        $query = PostView::where('post_id', $this->post->id);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('ip_address', request()->ip())
                  ->whereNull('user_id');
        }

        $viewedRecently = $query->where('viewed_at', '>', now()->subDay())->exists();

        if (!$viewedRecently) {
            $this->post->increment('views_count');

            PostView::create([
                'post_id'    => $this->post->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id'    => auth()->id(),
                'viewed_at'  => now(),
            ]);
        }
    }

    public function loadMore()
    {
        $this->perPage += 3;
    }

    public function with()
    {
        $categoryIds = $this->post->categories->pluck('id');
        
        if ($categoryIds->isEmpty()) {
            return ['relatedPosts' => collect(), 'hasMore' => false];
        }

        $relatedQuery = Post::query()
            ->where('status', 'published')
            ->where('id', '!=', $this->post->id) // Don't show the current post
            ->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds); // Share at least one category
            });

        $totalRelated = (clone $relatedQuery)->count();
        $relatedPosts = $relatedQuery
            ->with('user')
            ->latest()
            ->take($this->perPage)
            ->get();

        return [
            'relatedPosts' => $relatedPosts,
            'hasMore' => $totalRelated > $this->perPage,
        ];
    }
};
?>

<div>
    <x-slot:title>
        {{ $post->title }}
    </x-slot:title>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- MAIN CONTENT COLUMN -->
            <div class="lg:col-span-8">
                <article>
                    <!-- Back link -->
                    <div class="mb-6">
                        <a href="{{ route('blog.index') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            ← Back to posts
                        </a>
                    </div>

                    <!-- Featured Image -->
                    @if($post->featured_image)
                        <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-96 object-cover rounded-lg mb-8">
                    @endif

                    <!-- Post Header -->
                    <header class="mb-8">
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                            {{ $post->title }}
                        </h1>

                        <div class="flex items-center text-gray-600">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=4f46e5&color=fff" alt="{{ $post->user->name }}" class="w-10 h-10 rounded-full mr-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ $post->user->name }}</p>
                                <p class="text-sm">
                                    {{ $post->published_at->format('M d, Y') }} • 
                                    {{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min read • 
                                    {{ number_format($post->views_count) }} views
                                </p>
                            </div>
                        </div>

                        <!-- Categories and Tags -->
                        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-gray-200 mt-4">
                            @if($post->categories->count() > 0)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-500">Categories:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($post->categories as $category)
                                            <a href="{{ route('blog.index', ['category' => $category->slug]) }}" wire:navigate class="px-3 py-1 text-sm font-semibold rounded-full text-white hover:opacity-80 transition" style="background-color: {{ $category->color }}">
                                                {{ $category->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($post->tags->count() > 0)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-500">Tags:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($post->tags as $tag)
                                            <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800">
                                                #{{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </header>

                    <!-- Post Content -->
                    <div class="prose prose-lg prose-indigo max-w-none mb-12 ProseMirror">
                        {!! $post->content !!}
                    </div>

                    <!-- Post Footer -->
                    <footer class="border-t border-gray-200 pt-8 mb-12">
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=4f46e5&color=fff" alt="{{ $post->user->name }}" class="w-10 h-10 rounded-full mr-4">
                            <div>
                                <p class="font-medium text-gray-900">Written by {{ $post->user->name }}</p>
                                <p class="text-sm text-gray-600">Published on {{ $post->published_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </footer>
                </article>

                <!-- MOBILE RELATED POSTS (Shown only on small screens) -->
                <div class="lg:hidden mb-12">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-100">Related Posts</h3>
                    <div class="space-y-6">
                        @forelse($relatedPosts as $related)
                            <a href="{{ route('blog.show', $related->slug) }}" wire:navigate class="group block">
                                <div class="flex gap-4">
                                    @if($related->featured_image)
                                        <img src="{{ asset('storage/'.$related->featured_image) }}" class="w-24 h-24 object-cover rounded-lg shrink-0">
                                    @else
                                        <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $related->title }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ $related->published_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-gray-500 text-sm italic">No related posts found.</p>
                        @endforelse

                        @if($hasMore)
                            <div class="pt-4">
                                <button wire:click="loadMore" wire:loading.attr="disabled" class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="loadMore">Load More</span>
                                    <span wire:loading wire:target="loadMore">Loading...</span>
                                    <svg wire:loading wire:target="loadMore" class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Comment section -->
                <livewire:blog.comments :post="$post" />
            </div>

            <!-- DESKTOP SIDEBAR (Shown only on large screens) -->
            <aside class="hidden lg:block lg:col-span-4">
                <div class="sticky top-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-100">Related Posts</h3>
                    <div class="space-y-8">
                        @forelse($relatedPosts as $related)
                            <a href="{{ route('blog.show', $related->slug) }}" wire:navigate class="group block">
                                @if($related->featured_image)
                                    <img src="{{ asset('storage/'.$related->featured_image) }}" class="w-full h-40 object-cover rounded-lg mb-3 group-hover:opacity-90 transition-opacity">
                                @else
                                    <div class="w-full h-40 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors leading-tight">{{ $related->title }}</h4>
                                <p class="text-sm text-gray-500 mt-2">{{ $related->published_at->format('M d, Y') }}</p>
                            </a>
                        @empty
                            <p class="text-gray-500 text-sm italic">No related posts found.</p>
                        @endforelse

                        @if($hasMore)
                            <button wire:click="loadMore" wire:loading.attr="disabled" class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="loadMore">Load More</span>
                                <span wire:loading wire:target="loadMore">Loading...</span>
                                <svg wire:loading wire:target="loadMore" class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>

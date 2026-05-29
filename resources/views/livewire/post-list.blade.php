<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Latest Posts</h1>
            <p class="mt-2 text-lg text-gray-600">Thoughts, ideas, and stories from our team</p>
        </div>

            <!-- Success Messages -->
        @if (session('subscribe-success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
                <p class="text-sm text-green-800">{{ session('subscribe-success') }}</p>
            </div>
        @endif

        @if (session('unsubscribe-success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
                <p class="text-sm text-green-800">{{ session('unsubscribe-success') }}</p>
            </div>
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Search -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search posts..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                    @if($showSearchResults)
                        <div class="mt-3 relative z-20 min-h-[5rem] max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                            <div wire:loading wire:target="search">
                                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            @forelse ($searchedPosts as $searchedPost)
                               <a href="{{ route('blog.show', $searchedPost->slug) }}" class="block rounded-lg px-4 py-3 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500" wire:transition>
                                   <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $searchedPost->title }}</h3>
                               </a>
                            @empty
                                <div class="px-4 py-4 text-sm text-gray-500">No posts found</div>
                            @endforelse
                        </div>  
                    @endif    

                </div>

                <!-- Categories -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Categories</h3>
                    <div class="space-y-2">
                        <button wire:click="$set('selectedCategory', '')"
                            class="w-full text-left px-3 py-2 rounded-md text-sm {{ $selectedCategory === '' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                            All Categories
                        </button>
                        @foreach($categories as $category)
                            <button wire:click="$set('selectedCategory', '{{ $category->slug }}')"
                                class="w-full text-left px-3 py-2 rounded-md text-sm flex items-center justify-between {{ $selectedCategory === $category->slug ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                <span class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2"
                                        style="background-color: {{ $category->color }}"></span>
                                    {{ $category->name }}
                                </span>
                                <span class="text-xs text-gray-500">({{ $category->posts_count }})</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Tags -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            @if($tag->posts_count > 0)
                                <button wire:click="$set('selectedTag', '{{ $tag->slug }}')"
                                    class="px-3 py-1 rounded-full text-xs font-medium {{ $selectedTag === $tag->slug ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    {{ $tag->name }} ({{ $tag->posts_count }})
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Clear Filters -->
                 @if($search || $selectedCategory || $selectedTag)
                    <button wire:click="clearFilters"
                        class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                        Clear Filters
                    </button>
                @endif
            </aside>

            <div class="lg:col-span-3">
                 <!-- Posts Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($posts as $post)
                        <article wire:key="post-{{ $post->id }}"
                            class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200" wire:transition>
                            @if($post->featured_image)
                                <a href="{{ route('blog.show', $post->slug) }}" wire:navigate>
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}"
                                        class="w-full h-48 object-cover">
                                </a>
                            @else
                                <a href="{{ route('blog.show', $post->slug) }}" wire:navigate>
                                    <div
                                        class="w-full h-48 bg-gradient-to-br from-green-300 to-green-600 flex items-center justify-center">
                                        <span class="text-4xl text-white font-bold">{{ substr($post->title, 0, 1) }}</span>
                                    </div>
                                </a>
                            @endif

                            <div class="p-6">
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <span>{{ $post->published_at->format('M d, Y') }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $post->user->name }}</span>
                                </div>

                                @if($post->categories->count() > 0)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-500">Categories:</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($post->categories as $category)
                                                <a href="{{ route('home', ['category' => $category->slug]) }}" wire:navigate class="px-3 py-1 text-sm font-semibold rounded-full text-white hover:opacity-80 transition" style="background-color: {{ $category->color }}">
                                                    {{ $category->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-center text-sm text-gray-500 mb-3 mt-3">
                                    <span>{{ $post->comments_count  }}  {{Str::plural('comment', $post->comments_count) }}</span>
                                    @if ($post->views_count > 0)
                                        <span class="mx-2">•</span>
                                        <span>{{ $post->views_count }} {{ Str::plural('view',$post->views_count) }}</span>
                                        <span class="mx-1">•</span>
                                        <span>{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min read</span>
                                    @endif
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 mb-2">
                                    <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="hover:text-indigo-600">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                @if($post->excerpt)
                                    <p class="text-gray-600 text-sm mb-4">
                                        {{ Str::limit($post->excerpt, 120) }}
                                    </p>
                                @endif

                                <a href="{{ route('blog.show', $post->slug) }}" wire:navigate
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Read more →
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-500">No posts found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $posts->links() }}
        </div>

        <!-- Newsletter Subscription -->
        <div class="mt-16 bg-indigo-600 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-12 sm:px-12 sm:py-16 lg:flex lg:items-center lg:justify-between">
                <div class="lg:w-0 lg:flex-1">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Subscribe for our newsletter
                    </h2>
                    <p class="mt-4 max-w-3xl text-lg text-indigo-100">
                        Get the latest posts, ideas, and stories delivered directly to your inbox. Stay updated with our team.
                    </p>
                </div>
                <div class="mt-12 lg:mt-0 lg:ml-8">
                    <form wire:submit="sendCode">
                        <div class="sm:flex">
                            <label for="email-address" class="sr-only">Email address</label>
                            <input id="email-address" name="email-address" type="email" autocomplete="email" required 
                                wire:model.live.debounce.500ms="email"
                                class="w-full rounded-md border-white px-5 py-3 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 text-gray-900" 
                                placeholder="Enter your email">
                            <button type="submit" 
                                class="mt-3 flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-500 px-5 py-3 text-base font-medium text-white hover:bg-indigo-400 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 sm:mt-0 sm:ml-3 sm:w-auto sm:flex-shrink-0"
                                wire:loading.attr="disabled"
                                >

                                <span wire:loading.remove wire:target="sendCode">Subscribe</span>
                                <span wire:loading wire:target="sendCode">Please wait....</span>
                            </button>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </form>
                    <p class="mt-3 text-sm text-indigo-100">
                        We care about the protection of your data. Read our 
                        <a href="#" class="font-medium text-white underline">Privacy Policy.</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
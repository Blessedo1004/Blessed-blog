<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\WithFileUploads;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsLetterMail;

new class extends Component
{
    use WithFileUploads;
    #[Validate('required|string|min:3|max:255|unique:posts,title')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published')]
    public string $status = 'draft';

    #[Validate('required|array|min:1')]
    public array $selectedCategories = [];
    
    #[Validate('nullable|array')]
    public array $selectedTags = [];

    // get the categories and tags
    public function with(): array
    {
        return [
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ];
    }

    public function save(){
        $this->validate();

        $post = new Post();
        $post->user_id = auth()->id();
        $post->title = $this->title;
        $post->slug = Str::slug($this->title);
        $post->excerpt = $this->excerpt;
        $post->content = $this->content;
        $post->status = $this->status;

        if ($this->featured_image) {
            $path = $this->featured_image->store('posts','public');
            $post->featured_image = $path;
        }

        if ($this->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        // attach the categories
        $post->categories()->attach($this->selectedCategories);

        if (!empty($this->selectedTags)) {
            $post->tags()->attach($this->selectedTags);
        }

        if ($this->status === 'published') {
            $subscribers = Subscriber::get(['id', 'email']);
            if($subscribers){
                foreach($subscribers as $subscriber){
                    $email = $subscriber->email;
                    Mail::to($email)->send(new NewsLetterMail($post, $email));
                }
            }
        }
        
        session()->flash('success','Post created successfully!');

        $this->redirect(route('posts.index'), navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
        <p class="mt-1 text-sm text-gray-600">Write and publish your blog post</p>
    </div>

    {{-- form --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form wire:submit="save" class="space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Title
                </label>
                <input 
                    type="text"
                    id="title"
                    wire:model.live.debounce="title" 
                    placeholder="Enter post title"
                    autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700">
                    Excerpt
                </label>
                <textarea 
                    id="excerpt"
                    wire:model="excerpt" 
                    placeholder="A short summary of your post (optional)"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
                @error('excerpt')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">This will appear in post previews and search results</p>
            </div>

            <!-- Featured Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Featured Image
                </label>
                <input 
                    type="file" 
                    wire:model="featured_image"
                    accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100"
                />
                @error('featured_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                @if ($featured_image)
                    <div class="mt-3" wire:transition>
                        <img src="{{ $featured_image->temporaryUrl() }}" class="h-32 w-auto rounded border border-gray-300" alt="Preview">
                    </div>
                @endif
                
                <div wire:loading wire:target="featured_image" class="mt-2 text-sm text-gray-500">
                    Uploading...
                </div>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">
                    Content
                </label>

                {{-- <div wire:ignore>
                    <input type="hidden" name="content" id="x-content">
                    <trix-editor
                        input="x-content"
                        class="trix-content"
                        x-data
                        x-on:trix-change="$wire.content = $event.target.value"
                    ></trix-editor>
                </div> --}}

                {{-- <div wire:ignore
                    x-data="{
                        content: @entangle('content')
                    }"
                    x-init="
                        if (!tinymce.get('editor')) {
                            tinymce.init({
                                selector: '#editor',
                                height: 400,
                                menubar: false,
                                plugins: 'lists link image table code fontsize',
                                toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link image table | code |fontsize',

                                setup: function (editor) {

                                    editor.on('init', function () {
                                        editor.setContent(content || '');
                                    });

                                    editor.on('change keyup', function () {
                                        content = editor.getContent();
                                    });
                                }
                            });
                        }
                    "
                    class="mt-1"
                >
                    <textarea id="editor"></textarea> --}}
                {{-- </div> --}}

                <div wire:ignore>
                    <div
                        x-data="setupEditor($wire.entangle('content'))"
                        x-init="init($refs.editor)"
                        class="space-y-2"
                    >
                        <!-- Hidden input to hold the content for Livewire -->
                        <textarea wire:model="content" class="hidden"></textarea>

                        <!-- TOOLBAR -->
                        <div class="flex flex-wrap gap-1 border border-gray-300 rounded p-1 bg-gray-50 mb-2 items-center">
                            <!-- Text Formatting -->
                            <div class="flex gap-1 border-r border-gray-300 pr-1 mr-1">
                                <button type="button" @click="toggleBold()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('bold') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Bold">
                                    <span class="font-bold">B</span>
                                </button>
                                <button type="button" @click="toggleItalic()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('italic') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Italic">
                                    <span class="italic">I</span>
                                </button>
                                <button type="button" @click="toggleStrike()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('strike') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Strike">
                                    <span class="line-through">S</span>
                                </button>
                                <button type="button" @click="toggleCode()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('code') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Inline Code">
                                    <span class="font-mono text-xs"><></span>
                                </button>
                            </div>

                            <!-- Headings -->
                            <div class="flex gap-1 border-r border-gray-300 pr-1 mr-1">
                                <button type="button" @click="toggleHeading(1)" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('heading', { level: 1 }) ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Heading 1">H1</button>
                                <button type="button" @click="toggleHeading(2)" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('heading', { level: 2 }) ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Heading 2">H2</button>
                                <button type="button" @click="toggleHeading(3)" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('heading', { level: 3 }) ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Heading 3">H3</button>
                                <button type="button" @click="toggleHeading(4)" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('heading', { level: 4 }) ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Heading 4">H4</button>
                            </div>

                            <!-- Lists & Blocks -->
                            <div class="flex gap-1 border-r border-gray-300 pr-1 mr-1">
                                <button type="button" @click="toggleBulletList()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('bulletList') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Bullet List">• List</button>
                                <button type="button" @click="toggleOrderedList()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('orderedList') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Ordered List">1. List</button>
                                <button type="button" @click="toggleBlockquote()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('blockquote') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Blockquote">
                                    <span class="italic font-serif">"</span>
                                </button>
                                <button type="button" @click="toggleCodeBlock()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('codeBlock') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Code Block">Code</button>
                                <button type="button" @click="addImage()" class="p-2 rounded hover:bg-gray-200 transition-colors text-gray-700" title="Upload Image">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </button>
                                <button type="button" @click="addImageUrl()" class="p-2 rounded hover:bg-gray-200 transition-colors text-gray-700" title="Image from URL">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </button>
                                <button type="button" @click="setLink()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && isActive('link') ? 'bg-gray-300 text-indigo-700' : 'text-gray-700'" title="Add/Edit Link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </button>
                                <button type="button" @click="setHorizontalRule()" class="p-2 rounded hover:bg-gray-200 transition-colors text-gray-700" title="Horizontal Rule">—</button>
                            </div>

                            <!-- History -->
                            <div class="flex gap-1 ml-auto">
                                <button type="button" @click="undo()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && canUndo() ? 'text-gray-700' : 'text-gray-300 cursor-not-allowed'" :disabled="!canUndo()" title="Undo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </button>
                                <button type="button" @click="redo()" class="p-2 rounded hover:bg-gray-200 transition-colors" :class="updatedAt && canRedo() ? 'text-gray-700' : 'text-gray-300 cursor-not-allowed'" :disabled="!canRedo()" title="Redo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-18l-6 6m6-6l-6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- EDITOR -->
                        <div
                            x-ref="editor"
                            class="border border-gray-300 rounded p-4 min-h-[300px] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                        ></div>

                    </div>
                </div>

                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>



            <!-- Categories -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Categories (Required)
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                    @foreach($categories as $category)
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="selectedCategories" 
                                value="{{ $category->id }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <span class="ml-3 flex items-center">
                                <span 
                                    class="inline-block w-3 h-3 rounded-full mr-2" 
                                    style="background-color: {{ $category->color }}"
                                ></span>
                                <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('selectedCategories')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <!-- Tags -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tags (Optional)
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="selectedTags" 
                                value="{{ $tag->id }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <span class="ml-3 text-sm font-medium text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedTags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Select relevant tags to help readers find your content</p>
            </div>
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <div class="space-y-2">
                    <label class="flex items-start">
                        <input 
                            type="radio" 
                            wire:model="status" 
                            value="draft"
                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                        />
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-700">Draft</span>
                            <span class="block text-sm text-gray-500">Save as draft, not visible to readers</span>
                        </div>
                    </label>
                    
                    @can('publish posts')
                    <label class="flex items-start">
                        <input 
                            type="radio" 
                            wire:model="status" 
                            value="published"
                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                        />
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-gray-700">Published</span>
                            <span class="block text-sm text-gray-500">Publish immediately, visible to all readers</span>
                        </div>
                    </label>
                    @endcan
                </div>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Create Post
                </button>
                <a 
                    href="{{ route('posts.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
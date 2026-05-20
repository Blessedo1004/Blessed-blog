<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'category')]
    public string $selectedCategory = '';

    #[Url(as: 'tag')]
    public string $selectedTag = '';

    public $showSearchResults = false;

    #[Layout('layouts.public')]
    #[Title('Blessed Blog')]
    public function render()
    {
        $posts = Post::with(['user','categories','tags'])->withCount('comments')
        ->where('status','published')
        ->when($this->selectedCategory, function($query){
            $query->whereHas('categories',function($q){
                $q->where('slug',$this->selectedCategory);
            });
        })
        ->when($this->selectedTag, function($query){
            $query->whereHas('tags',function($q){
                $q->where('slug',$this->selectedTag);
            });
        })
        ->latest('published_at')
        ->paginate(9);

        if($this->search){
        $searchedPosts = Post::where('status','published')->select(['id','title','slug'])
        ->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('content', 'like', '%'.$this->search.'%')
                ->orWhere('excerpt', 'like', '%'.$this->search.'%');
            })->get();

            $this->showSearchResults = true;
        }

        else if($this->search === ''){
            $this->showSearchResults = false;
        }

        return view('livewire.post-list',[
            'posts' => $posts,
            'searchedPosts' => $searchedPosts ?? [],
            'categories' => Category::withCount('posts')->get(),
            'tags' => Tag::withCount('posts')->get(),
        ]);
    }

    public function updatingSearch(){
        $this->resetPage();
    }

     public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedTag(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->selectedTag = '';
        $this->resetPage();
    }
}
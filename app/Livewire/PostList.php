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
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriberEmailVerificationMail;
use Illuminate\Support\Facades\Cache;

class PostList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Validate('required|string|email|unique:subscribers,email')]    
    public string $email;

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
        ->paginate(10);

        if($this->search){
            $searchedPosts = Post::where('status','published')->select(['id','title','slug'])
            ->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%')
                    ->orWhere('excerpt', 'like', '%'.$this->search.'%');
                })->paginate(10)->onEachSide(0);

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

    public function sendCode (){
        $this->validate();

        $existingCode = Cache::get("verify-email-token-{$this->email}");

        if($existingCode){
            Cache::forget("verify-email-for-{$existingCode}");
            Cache::forget("verify-email-token-{$this->email}");
       }

        $code = Str::random(6);
        Cache::put("verify-email-for-{$code}", $this->email, 15 * 60);
        Cache::put("verify-email-token-{$this->email}", $code, 15 * 60);
        Mail::to($this->email)->send(new SubscriberEmailVerificationMail($code));
        session()->flash('email', $this->email);
        $this->redirect(route('verify-email'), navigate:true);
    }
}
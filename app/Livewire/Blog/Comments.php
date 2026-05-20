<?php

namespace App\Livewire\Blog;

use App\Models\Comment;
use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use App\Mail\CommentNotification;
use Illuminate\Support\Facades\Mail;

class Comments extends Component
{
    public Post $post;

    #[Validate('required|string|min:3|max:1000')]
    public string $newComment = '';

    public ?int $replyingTo = null;

    #[Validate('required|string|min:3|max:1000')]
    public string $replyContent = '';

    public function mount(Post $post){
        $this->post = $post;
    }

    public function postComment(){
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate(['newComment' => 'required|string|min:3|max:1000']);

        $comment = Comment::create([
            'post_id' => $this->post->id,
            'user_id' => Auth::id(),
            'content' => $this->newComment,
            'status' => 'approved',
        ]);

        $this->newComment = '';

        $comment->load(['post', 'user']);

        if($this->post->user_id !== Auth::id()){
            Mail::to($this->post->user->email)->send(new CommentNotification($comment));
        }
        
        // $this->dispatch('comment-posted');

        session()->flash('comment-success','comment posted successfully!');
    }
    
    public function startReply($commentId){
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->replyingTo = $commentId;
        $this->replyContent = '';
    }

    public function cancelReply(){
        $this->replyingTo = null;
        $this->replyContent = '';
    }

    public function postReply($parentId){
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate(['replyContent' => 'required|string|min:3|max:1000']);

        $comment = Comment::create([
            'post_id' => $this->post->id,
            'user_id' => Auth::id(),
            'parent_id' => $parentId,
            'content' => $this->replyContent,
            'status' => 'approved',
        ]);

        $this->replyingTo = null;
        $this->replyContent = '';

        $comment->load(['post', 'user']);
        // $this->dispatch('comment-posted');
            if($this->post->user_id !== Auth::id()){
            Mail::to($this->post->user->email)->send(new CommentNotification($comment));
        }
        session()->flash('comment-success','Reply posted successfully!');
    }

    public function deleteComment(Comment $comment){
        if($comment->user_id != Auth::id()){
            session()->flash('delete-error', 'You cannot delete this comment');
            return;
        }
        $comment->delete();
        session()->flash('delete-success', 'Comment deleted Successfully');
    }

    // #[On('comment-posted')]
    public function render()
    {
        $comments = Comment::where('post_id', $this->post->id)
        ->approved()
        ->topLevel()
        ->with(['user','replies.user'])
        ->latest()
        ->get();


        return view('livewire.blog.comments',[
            'comments' => $comments
        ]);
    }
}
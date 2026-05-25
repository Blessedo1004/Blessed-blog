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

    public Comment $comment;

    public Comment $reply;

    #[Validate('required|string|min:3|max:1000')]
    public string $newComment = '';

    public ?int $replyingTo = null;

    public ?int $repliesFor = null;

    public array $expandedComments = [];
    
    // public $moreComments = 5;
    // public $loadAmount = 5;
    // public $maxId = null;

    // cursor state
    public array $commentsIds = [];

    public $isCommentEdit = false;

    public $isReplyEdit = false;

    public array $repliesPagination = [];

    #[Validate('required|string|min:3|max:1000')]
    public string $replyContent = '';

    public function mount(Post $post){
        $this->post = $post;
        $this->loadMoreComments(); // Initial batch
    } 

    /*
    public function loadMoreComments(){
        $this->loadAmount += 5;
    }
    */

    public function loadMoreComments()
    {
        $query = Comment::where('post_id', $this->post->id)
            ->approved()
            ->topLevel();

        // The 'Cursor' logic: only get IDs older than the ones we already have
        if (!empty($this->commentsIds)) {
            $query->where('id', '<', min($this->commentsIds));
        }

        $newIds = $query->latest()
            ->take(5)
            ->pluck('id')
            ->toArray();

        array_push($this->commentsIds, ...$newIds);
    }

    public function loadMoreReplies($commentId)
    {
        $this->repliesPagination[$commentId] = ($this->repliesPagination[$commentId] ?? 3) + 3;
    }

    public function cancelEdit()
    {
        $this->isCommentEdit = false;
        $this->isReplyEdit = false;
        $this->newComment = '';
        $this->replyContent = '';
        $this->replyingTo = null;
    }

    public function postComment(){
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate(['newComment' => 'required|string|min:3|max:1000']);

        if ($this->isCommentEdit){
            $this->comment->content = $this->newComment;
            $this->comment->save();
            $this->isCommentEdit = false;
            $this->newComment = '';
            session()->flash('comment-edit-success','comment edited successfully!');
            return;
        }

        $comment = Comment::create([
            'post_id' => $this->post->id,
            'user_id' => Auth::id(),
            'content' => $this->newComment,
            'status' => 'approved',
        ]);

        $this->newComment = '';

        // Add to cursor list
        array_unshift($this->commentsIds, $comment->id);

        $comment->load(['post', 'user']);

        if($this->post->user_id !== Auth::id()){
            Mail::to($this->post->user->email)->send(new CommentNotification($comment));
        }
        
        // $this->dispatch('comment-posted');

        session()->flash('comment-success','comment posted successfully!');
    }


    public function showCommentEdit (Comment $comment){
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->comment = $comment;
        $this->newComment = $comment->content;
        $this->isCommentEdit = true;
    }

    public function showReplyEdit (Comment $comment){
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->reply = $comment;
        $this->replyContent = $comment->content;
        $this->isReplyEdit = true;
        $this->replyingTo = $comment->parent_id;
    }


    public function showReplies($commentId)
    {
        $this->repliesFor = $commentId;
    }

    public function hideReplies()
    {
        $this->repliesFor = null;
    }

    public function toggleExpand($id)
    {
        if (in_array($id, $this->expandedComments)) {
            $this->expandedComments = array_diff($this->expandedComments, [$id]);
        } else {
            $this->expandedComments[] = $id;
        }
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
        $this->isReplyEdit = false;
    }

    public function postReply($parentId){
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate(['replyContent' => 'required|string|min:3|max:1000']);

        if ($this->isReplyEdit){
            $this->reply->content = $this->replyContent;
            $this->reply->save();
            $this->isReplyEdit = false;
            $this->replyingTo = null;
            $this->replyContent = '';
            $this->repliesFor = $parentId;
            session()->flash('reply-edit-success','reply edited successfully!');
            return;
        }

        $comment = Comment::create([
            'post_id' => $this->post->id,
            'user_id' => Auth::id(),
            'parent_id' => $parentId,
            'content' => $this->replyContent,
            'status' => 'approved',
        ]);

        $this->replyingTo = null;
        $this->repliesFor = $parentId;
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

        // Remove from cursor list
        $this->commentsIds = array_diff($this->commentsIds, [$comment->id]);

        $comment->delete();
        session()->flash('delete-success', 'Comment deleted Successfully');
    }

    // #[On('comment-posted')]
    /*
    public function render()
    {
        $query = Comment::where('post_id', $this->post->id)
            ->approved()
            ->topLevel();

        $totalCount = $query->count();

        $comments = $query->with(['user','replies.user'])
            ->latest()
            ->take($this->moreComments)
            ->get();


        return view('livewire.blog.comments',[
            'comments' => $comments,
            'totalCommentsCount' => $totalCount
        ]);
    }
    */

    public function render()
    {
        // TRUE CURSOR RENDERING
        // We only fetch the comments that match our stored IDs.
        // This ensures the database query is small and stable.
        $comments = Comment::whereIn('id', $this->commentsIds)
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        // Total count for the 'Load More' button visibility
        $totalCommentsCount = Comment::where('post_id', $this->post->id)
            ->approved()
            ->topLevel()
            ->count();

        return view('livewire.blog.comments', [
            'comments' => $comments,
            'totalCommentsCount' => $totalCommentsCount,
            'moreComments' => count($this->commentsIds),
        ]);
    }
}
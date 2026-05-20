<h2>New Comment Posted</h2>
<p>Hi Author, a new comment has been posted on your post: "{{ $comment->post->title }}"</p>
<p>Commenter: {{ $comment->user->name }}</p>
<p>Comment: {{ $comment->content }}</p>
<a href="{{ route('blog.show', $comment->post->slug) }}">View Post</a>

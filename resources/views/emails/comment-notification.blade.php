<h2>New Comment Posted</h2>
<p>Hi Author, a new comment has been posted on your post: "{{ $comment->post->title }}"</p>
<p>Commenter: {{ $comment->user->name }}</p>
<p>Comment: {{ $comment->content }}</p>
<a href=" http://127.0.0.1:8000/blog/{{ $comment->post->slug }}" target="_blank">View Post</a>

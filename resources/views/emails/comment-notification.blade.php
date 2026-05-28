<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment on "{{ $comment->post->title }}"</title>
    <style>
        /* Email client resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; display: block; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; }

        /* Component Styles */
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; }
        .main { background-color: #ffffff; margin: 20px auto; width: 100%; max-width: 600px; border-spacing: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .header { padding: 30px; text-align: center; background-color: #4f46e5; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }

        .content { padding: 40px 30px; }
        .content h2 { margin: 0 0 10px 0; color: #111827; font-size: 20px; line-height: 1.2; font-weight: 700; }
        .content .post-title { color: #4f46e5; font-weight: 600; margin-bottom: 25px; display: block; text-decoration: none; }
        
        .comment-box { background-color: #f9fafb; border-left: 4px solid #4f46e5; padding: 20px; border-radius: 4px; margin-bottom: 30px; }
        .commenter-info { margin-bottom: 15px; display: flex; align-items: center; }
        .commenter-name { font-weight: 700; color: #111827; font-size: 16px; }
        .comment-text { color: #4b5563; font-size: 16px; line-height: 1.6; font-style: italic; }

        .button-container { text-align: center; }
        .button { background-color: #4f46e5; color: #ffffff !important; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; font-size: 16px; transition: background-color 0.2s; }
        .button:hover { background-color: #4338ca; }
        .button:visited { color: #ffffff !important; }

        .footer { padding: 30px; text-align: center; font-size: 13px; color: #9ca3af; }
        .footer p { margin: 10px 0; }

        /* Responsive */
        @media screen and (max-width: 600px) {
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center">
            <!-- Header -->
            <tr>
                <td class="header">
                    <h1>{{ config('app.name') }}</h1>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="content">
                    <h2>New Comment Posted</h2>
                    <p>Hi, a new comment has been posted on your post:</p>
                    <a href="{{ route('blog.show', $comment->post->slug) }}" class="post-title">{{ $comment->post->title }}</a>

                    <div class="comment-box">
                        <div class="commenter-info">
                            <span class="commenter-name">{{ $comment->user->name }}</span>
                        </div>
                        <div class="comment-text">
                            "{{ $comment->content }}"
                        </div>
                    </div>
                    
                    <div class="button-container">
                        <a href="{{ route('blog.show', $comment->post->slug) }}?c={{ $comment->id }}#comment-{{ $comment->id }}" class="button">View & Reply to Comment</a>
                    </div>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p>You received this email because you are the author of this post.</p>
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

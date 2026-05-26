<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }}</title>
    <style>
        /* Email client resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; display: block; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; }

        /* Component Styles */
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .header { padding: 30px; text-align: center; background-color: #4f46e5; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }

        .featured-image { width: 100%; max-width: 600px; }

        .content { padding: 40px 30px; }
        .content h2 { margin: 0 0 20px 0; color: #111827; font-size: 28px; line-height: 1.2; font-weight: 800; }
        .content p { margin: 0 0 25px 0; color: #4b5563; font-size: 16px; line-height: 1.6; }

        .button-container { text-align: left; }
        .button { background-color: #4f46e5; color: #ffffff !important; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; font-size: 16px; transition: background-color 0.2s; }
        .button:hover { background-color: #4338ca; }
        .button:visited { color: #ffffff !important; }

        .footer { padding: 30px; text-align: center; font-size: 13px; color: #9ca3af; }
        .footer a { color: #4f46e5 !important; text-decoration: underline; }
        .footer a:visited { color: #4f46e5 !important; }
        .footer p { margin: 10px 0; }

        /* Responsive */
        @media screen and (max-width: 600px) {
            .content { padding: 30px 20px; }
            .content h2 { font-size: 24px; }
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

            <!-- Featured Image -->
            @if($post->featured_image)
            <tr>
                <td>
                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="featured-image">
                </td>
            </tr>
            @endif

            <!-- Body -->
            <tr>
                <td class="content">
                    <h2>{{ $post->title }}</h2>
                    <p>{{$post->user->name}}</p>
                    <p>{{ $post->published_at->diffForHumans() }}</p>
                    <p>{{  Str::limit(strip_tags($post->content), 150) }}</p>
                    
                    <div class="button-container">
                        <a href="{{ route('blog.show', $post->slug) }}" class="button">Read the full post</a>
                    </div>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p>You received this email because you subscribed to our newsletter.</p>
                    <p>
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                        <a href="{{ url('/') }}">Visit our Blog</a> | <a href="{{ route('unsubscribe', $email) }}" target="_blank">Unsubscribe</a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AgroSense</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <div class="auth-card">
        <a href="/" class="brand">
            <i class="fas fa-leaf"></i> AgroSense
        </a>

        <div class="header">
            <h2>Reset Password</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <ul style="list-style-position: inside; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <div class="form-group">
                <div class="input-wrapper">
                    <i class="far fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email address" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Send Reset Link
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>

</body>
</html>


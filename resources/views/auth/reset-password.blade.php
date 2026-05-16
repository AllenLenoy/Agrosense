<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AgroSense</title>
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
            <h2>Set New Password</h2>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul style="list-style-position: inside; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <div class="input-wrapper">
                    <i class="far fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email ?? '') }}" placeholder="Email address" required readonly>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-control" placeholder="New Password" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm New Password" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Reset Password
            </button>
        </form>
    </div>

</body>
</html>


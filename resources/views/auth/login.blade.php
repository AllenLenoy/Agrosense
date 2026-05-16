<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgroSense</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <div class="auth-container">
        
        <!-- Left Side content (from the design) -->
        <div class="auth-left">
            <div class="auth-left-content">
                <a href="/" class="brand">
                    <i class="fas fa-leaf"></i> AgroSense
                </a>
                
                <div class="hero-text">
                    <h1>Smart Farming Starts Here <i class="fas fa-leaf text-green-400"></i></h1>
                    <p>Monitor your farm, analyze real-time data, and make smarter decisions for a better tomorrow.</p>
                </div>
                
                <ul class="features-list">
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-broadcast-tower"></i></div>
                        Real-time Monitoring
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        Smart Insights
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-tint"></i></div>
                        Water Management
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon"><i class="fas fa-seedling"></i></div>
                        Higher Productivity
                    </li>
                </ul>
            </div>
            
            <div class="quote-box">
                <p>"Technology grounded in nature, growth powered by intelligence."</p>
            </div>
        </div>

        <!-- Right Side form -->
        <div class="auth-right">
            <div class="auth-form-wrapper">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Login to continue to your AgroSense account</p>
                </div>

                @if ($errors->any())
                    <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <ul style="list-style-position: inside; margin: 0; padding: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="far fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Email address" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <button type="button" class="password-toggle"><i class="far fa-eye-slash"></i></button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-submit">
                        Login <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-footer" style="margin-top: 1.5rem;">
                    Don't have an account? <a href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
            
            <!-- Bottom feature cards that appear in the image container -->
            <!-- In the actual implementation, to avoid clutter we just put them absolute if screen is wide enough -->
        </div>
    </div>

</body>
</html>


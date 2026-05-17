<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - AgroSense</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <div class="auth-container">
        
        <!-- Left Side content -->
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
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Create Account</h2>
                    <p>Join AgroSense and grow smarter</p>
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

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" name="name" class="form-control" placeholder="Full name" value="{{ old('name') }}" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="far fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Email address" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-phone-alt input-icon" style="opacity: 0.7; font-size: 0.9em;"></i>
                            <input type="tel" name="phone" class="form-control" placeholder="Phone number (optional)" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="role-selection">
                            <label class="role-card active" id="role-farmer-card">
                                <input type="radio" name="role" value="farmer" checked onclick="selectRole('farmer')">
                                <div class="role-card-icon"><i class="fas fa-seedling"></i></div>
                                <div class="role-card-text">Farmer</div>
                                <div class="role-card-desc">Manage crops & irrigation</div>
                            </label>
                            <label class="role-card" id="role-admin-card">
                                <input type="radio" name="role" value="admin" onclick="selectRole('admin')">
                                <div class="role-card-icon"><i class="fas fa-user-shield"></i></div>
                                <div class="role-card-text">Admin</div>
                                <div class="role-card-desc">Manage system & hardware</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <button type="button" class="password-toggle"><i class="far fa-eye-slash"></i></button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                            <button type="button" class="password-toggle"><i class="far fa-eye-slash"></i></button>
                        </div>
                    </div>

                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="terms" required>
                        <span>I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></span>
                    </label>

                    <button type="submit" class="btn-submit">
                        Create Account <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Role selection toggle
        function selectRole(role) {
            document.querySelectorAll('.role-card').forEach(card => card.classList.remove('active'));
            document.getElementById(`role-${role}-card`).classList.add('active');
        }

        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon  = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>


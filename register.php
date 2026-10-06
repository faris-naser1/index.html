<?php
session_start();

/* ===== DATABASE CONNECTION ===== */
$host = "localhost";
$db = "ticketing_system";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

/* ===== HANDLE REGISTRATION ===== */
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = "Username or email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = "Registration successful! You can now login.";
                $_SESSION['reg_success'] = $success;
                header("Location: login.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MatchTicketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== Page Styles ===== */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .card-header {
            background: linear-gradient(to right, #1a237e, #283593);
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: none;
        }

        .card-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .card-body {
            padding: 2.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #283593;
            box-shadow: 0 0 0 0.2rem rgba(40, 53, 147, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            color: #666;
        }

        .btn-register {
            background: linear-gradient(to right, #1a237e, #283593);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 53, 147, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            color: #666;
        }

        .login-link a {
            color: #283593;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .feature-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .feature-item i {
            font-size: 2rem;
            color: #283593;
            margin-bottom: 0.5rem;
        }

        .feature-item p {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
        }

        .password-strength {
            height: 5px;
            background: #eee;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }

        .strength-weak { background: #ff4757; }
        .strength-fair { background: #ffa502; }
        .strength-good { background: #2ed573; }
        .strength-strong { background: #1e90ff; }

        /* ===== Animation ===== */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .card-header h1 {
                font-size: 2rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card fade-in">
            <div class="card-header">
                <h1><i class="fas fa-ticket-alt"></i> MatchTicketing</h1>
                <p>Create your account to book match tickets</p>
            </div>
            
            <div class="card-body">
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['reg_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['reg_success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['reg_success']); ?>
                <?php endif; ?>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-md-6 fade-in delay-1">
                            <label for="full_name" class="form-label">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter your full name.
                                </div>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="col-md-6 fade-in delay-1">
                            <label for="username" class="form-label">
                                <i class="fas fa-user-tag"></i> Username
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user-tag"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please choose a username.
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 fade-in delay-2">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 fade-in delay-2">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter your phone number.
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6 fade-in delay-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       oninput="checkPasswordStrength()">
                                <div class="invalid-feedback">
                                    Please enter a password (min 6 characters).
                                </div>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <small class="text-muted" id="strengthText">Password strength</small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6 fade-in delay-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Confirm Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       oninput="checkPasswordMatch()">
                                <div class="invalid-feedback">
                                    Please confirm your password.
                                </div>
                            </div>
                            <div class="mt-2" id="passwordMatch"></div>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="form-check mt-4 fade-in delay-1">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="terms" 
                               required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                        </label>
                        <div class="invalid-feedback">
                            You must agree to the terms and conditions.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register mt-4 fade-in delay-2">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <!-- Features Section -->
                <div class="features fade-in delay-3">
                    <div class="feature-item">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Book Match Tickets</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-hotel"></i>
                        <p>Reserve Hotels</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bus"></i>
                        <p>Arrange Transport</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <p>Secure Booking</p>
                    </div>
                </div>

                <!-- Login Link -->
                <div class="login-link fade-in delay-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By registering with MatchTicketing, you agree to:</p>
                    <ul>
                        <li>Provide accurate information</li>
                        <li>Use the service responsibly</li>
                        <li>Accept our booking policies</li>
                        <li>Comply with all applicable laws</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>We respect your privacy and are committed to protecting your personal data:</p>
                    <ul>
                        <li>We collect only necessary information</li>
                        <li>Your data is securely stored</li>
                        <li>We don't share your data with third parties</li>
                        <li>You can request data deletion anytime</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            // Check length
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Check character variety
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update display
            let width = 0;
            let color = '';
            let text = 'Password strength';
            let textColor = '#666';
            
            if (password.length === 0) {
                width = 0;
                text = 'Password strength';
            } else if (strength <= 2) {
                width = 25;
                color = 'strength-weak';
                text = 'Weak';
                textColor = '#ff4757';
            } else if (strength <= 4) {
                width = 50;
                color = 'strength-fair';
                text = 'Fair';
                textColor = '#ffa502';
            } else if (strength <= 6) {
                width = 75;
                color = 'strength-good';
                text = 'Good';
                textColor = '#2ed573';
            } else {
                width = 100;
                color = 'strength-strong';
                text = 'Strong';
                textColor = '#1e90ff';
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.className = 'strength-bar ' + color;
            strengthText.textContent = text;
            strengthText.style.color = textColor;
        }

        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Passwords match</small>';
            } else {
                matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</small>';
            }
        }

        // Auto-check on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkPasswordStrength();
            checkPasswordMatch();
        });
    </script>
</body>
</html>
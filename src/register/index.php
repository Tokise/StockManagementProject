<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../modules/index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../modules/config/db.php';
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = "Username is required";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors['username'] = "Username must be 3-20 characters and contain only letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }
    
    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    // Validate full name
    if (empty($full_name)) {
        $errors['full_name'] = "Full name is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if username exists
            $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
            if (fetchValue($sql, [$username]) > 0) {
                $errors['username'] = "Username already exists";
            }
            
            // Check if email exists
            $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
            if (fetchValue($sql, [$email]) > 0) {
                $errors['email'] = "Email already exists";
            }
            
            if (empty($errors)) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $data = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'full_name' => $full_name,
                    'role' => 'employee' // Default role
                ];
                
                $user_id = insert('users', $data);
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'employee';
                
                // Redirect to dashboard
                header("Location: ../modules/index.php");
                exit();
            }
        } catch (Exception $e) {
            $error = "An error occurred. Please try again later.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexInvent - Register</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #7f8c8d;
            margin-bottom: 0;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-register {
            background: #3498db;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .form-label {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .input-group-text {
            background: none;
            border-left: none;
            cursor: pointer;
        }
        
        .password-toggle {
            border: 1px solid #e0e0e0;
            border-left: none;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>NexInvent</h1>
            <p>Create your account</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                       id="full_name" name="full_name" required 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <?php if (isset($errors['full_name'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['full_name']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                       id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['username']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                       id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['email']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                           id="password" name="password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </span>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['password']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                           id="confirm_password" name="confirm_password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="bi bi-eye"></i>
                    </span>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($errors['confirm_password']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-register w-100">
                Create Account
            </button>
            
            <div class="text-center mt-4">
                <a href="../login/index.php" class="text-decoration-none">
                    Already have an account? Login here
                </a>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const icon = passwordInput.nextElementSibling.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
    </script>
</body>
</html>
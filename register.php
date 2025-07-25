<?php
session_start();
include "db.php";

$message = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validate password strength
    $errors = [];
    if (strlen($password) < 8) $errors[] = "at least 8 characters";
    if (!preg_match("/[A-Z]/", $password)) $errors[] = "one uppercase letter";
    if (!preg_match("/[a-z]/", $password)) $errors[] = "one lowercase letter";
    if (!preg_match("/[0-9]/", $password)) $errors[] = "one number";
    if (!preg_match("/[^A-Za-z0-9]/", $password)) $errors[] = "one special character";

    if (!in_array($role, ['admin', 'author', 'subscriber'])) {
        $errors[] = "valid role selection";
    }

    if (!empty($errors)) {
        $message = "❌ Password must contain: " . implode(", ", $errors) . ".";
    } else {
        // Check for duplicate username
        $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $name, $hashed_password, $role);
            if ($insert->execute()) {
                // ✅ Redirect to login after success
                header("Location: login.php");
                exit;
            } else {
                $message = "❌ Registration failed. Please try again.";
            }
            $insert->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register - With Role</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            width: 420px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        .logo {
            width: 100%;
            height: 100px;
            background: linear-gradient(45deg, #a18cd1, #fbc2eb);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .welcome-text {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            color: #718096;
            font-size: 16px;
            text-align: center;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            background: #fafafa;
            color: #2d3748;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #a18cd1;
            background: white;
            box-shadow: 0 0 0 3px rgba(161, 140, 209, 0.2);
        }

        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #a18cd1, #fbc2eb);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(161, 140, 209, 0.4);
        }

        .message {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 14px;
        }

        .message.success {
            color: #2f855a;
            border-color: #c6f6d5;
            background: #f0fff4;
        }

        .message a {
            color: #805ad5;
            font-weight: 600;
            text-decoration: none;
        }

        .login-redirect {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #718096;
        }

        .login-redirect a {
            color: #a18cd1;
            text-decoration: none;
            font-weight: 600;
        }

        .login-redirect a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-card {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo">Create Account</div>
        <h1 class="welcome-text">Join Us</h1>
        <p class="subtitle">Fill the form to register</p>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name">Username</label>
                <input type="text" name="name" class="form-control" id="name" placeholder="Choose a username" required />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Create a strong password" required />
            </div>

            <div class="form-group">
                <label for="role">Select Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="" disabled selected>Choose a role</option>
                    <option value="admin">Admin</option>
                    <option value="author">Author</option>
                    <option value="subscriber">Subscriber</option>
                </select>
            </div>

            <button type="submit" name="submit" class="btn-register">Register</button>
        </form>

        <?php if ($message): ?>
            <div class="message <?= str_contains($message, '✅') ? 'success' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="login-redirect">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>

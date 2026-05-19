<?php
session_start();

$host = "sql310.infinityfree.com";
$user = "if0_41890353";
$pass = "FinalProject001"; 
$dbname = "if0_41890353_pc_builder_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; 
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        if ($password == $user_data['password']) { 
            
            $_SESSION['user_id'] = $user_data['user_id'] ?? $user_data['id'] ?? null; 
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = isset($user_data['role']) ? strtolower(trim($user_data['role'])) : 'user';

            if ($_SESSION['user_id'] !== null) {
                // Lahat sila didiretso sa home.php ngayon para parehas ang interface
                header("Location: home.php");
                exit();
            } else {
                $error = "System Error: Cannot see the ID column on users table.";
            }
        } else {
            $error = "Wrong password! Check if your typing is correct.";
        }
    } else {
        $error = "Can't find the username: " . htmlspecialchars($username);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #121212; 
            color: #ffffff; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .login-card { 
            background: #1e1e1e; 
            padding: 2.5rem; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.8); 
            width: 100%; 
            max-width: 400px; 
            border: 1px solid #0d6efd; 
        }
        .form-label {
            color: #add8e6 !important; 
            font-weight: 600;
        }
        .form-control { 
            background: #2b2b2b; 
            border: 1px solid #0d6efd; 
            color: #ffffff !important; 
            padding: 12px;
        }
        .form-control:focus { 
            background: #333; 
            color: #ffffff; 
            border-color: #add8e6; 
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.5); 
        }
        .form-control::placeholder {
            color: #777777 !important;
        }
        .btn-primary { 
            padding: 12px; 
            font-size: 1.1rem;
            background: #0d6efd;
            border: none;
            transition: 0.3s;
            color: #ffffff !important;
        }
        .btn-primary:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.6);
        }
        .brand-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #ffffff; 
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .pc-text {
            color: #add8e6;
        }
        .text-muted {
            color: #add8e6 !important; 
            opacity: 0.7;
        }
        .register-link {
            color: #ffffff !important; 
            text-decoration: none;
            transition: 0.3s;
            font-weight: 600;
        }
        .register-link:hover {
            color: #add8e6 !important;
            text-decoration: underline;
        }
        .text-info {
            color: #add8e6 !important;
        }
        .border-secondary {
            border-color: #0d6efd !important; 
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #121212; }
        ::-webkit-scrollbar-thumb { background: #0d6efd; border-radius: 10px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-logo mb-1">Synthesis <span class="pc-text">PC</span></div>
        <p class="text-muted small">Sign in to your account</p>
    </div>
    
    <?php if($error != ""): ?>
        <div class="alert alert-danger py-2 text-center small" style="border-radius: 8px; border: none; background-color: rgba(220, 53, 69, 0.2); color: #f8d7da;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold">USERNAME</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">PASSWORD</label>
            <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">SIGN IN</button>
    </form>

    <div class="text-center mt-3">
        <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="register-link">Create Account</a></p>
    </div>
    
    <div class="text-center mt-4 pt-3 border-top border-secondary">
        <p class="small text-muted mb-0">Database: <span class="text-info">InfinityFree SQL</span></p>
    </div>
</div>

</body>
</html>

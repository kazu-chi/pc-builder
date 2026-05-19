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
$success = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "The password doesnt match!";
    } else {
        $check_user = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
        $result = $conn->query($check_user);

        if ($result && $result->num_rows > 0) {
            $error = "This username is already taken.";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
            if ($conn->query($sql)) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Error: Can't create the account. Try again.";
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
    <title>Create Account - Synthesis PC</title>
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
        .btn-primary-custom { 
            padding: 12px; 
            font-size: 1.1rem;
            background: #0d6efd; 
            border: none;
            transition: 0.3s;
            color: #ffffff !important;
            border-radius: 6px;
        }
        .btn-primary-custom:hover {
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
        .login-link {
            color: #ffffff !important; 
            text-decoration: none;
            transition: 0.3s;
            font-weight: 600;
        }
        .login-link:hover {
            color: #add8e6 !important;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-logo mb-1">Synthesis <span class="pc-text">PC</span></div>
        <p class="text-muted small">Join Synthesis PC Builder</p>
    </div>
    
    <?php if($error != ""): ?>
        <div class="alert alert-danger py-2 text-center small" style="border-radius: 8px; border: none; background-color: rgba(220, 53, 69, 0.2); color: #f8d7da;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if($success != ""): ?>
        <div class="alert alert-success py-2 text-center small" style="border-radius: 8px; border: none; background-color: rgba(13, 110, 253, 0.2); color: #add8e6;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold">CHOOSE USERNAME</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">PASSWORD</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">CONFIRM PASSWORD</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary-custom w-100 fw-bold">REGISTER</button>
    </form>

    <div class="text-center mt-3">
        <p class="small text-muted mb-0">Already have an account? <a href="index.php" class="login-link">Sign In here</a></p>
    </div>
</div>

</body>
</html>

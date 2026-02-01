<?php
session_start();
require '../function.php';

// Cek Cookie
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];
    $result = mysqli_query($conn, "SELECT email FROM users WHERE user_id = $id");
    $row = mysqli_fetch_assoc($result);
    if ($key === hash('sha256', $row['email'])) {
        $_SESSION['login'] = true;
    }
}

// Cek Session
if (isset($_SESSION['login'])) {
    if ($_SESSION['login']['role'] == 'admin') {
        header("Location: ../admin/index.php");
    } elseif ($_SESSION['login']['role'] == 'developer') {
        header("Location: ../developer/index.php");
    } else {
        header("Location: ../dashboard.php");
    }
    exit;
}

// LOGIK LOGIN
if (isset($_POST["login"])) {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row["password"])) {
            $_SESSION["login"] = [
                'user_id' => $row['user_id'],
                'email' => $row['email'],
                'name' => $row['name'],
                'role' => $row['role']
            ];

            if (isset($_POST['remember'])) {
                setcookie('id', $row['user_id'], time() + 60, '/');
                setcookie('key', hash('sha256', $row['email']), time() + 60, '/');
            }

            if ($row['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($row['role'] == 'developer') {
                header("Location: ../developer/index.php");
            } else {
                header("Location: ../dashboard.php");
            }
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | BugTracker</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">

    <style>
        /* --- CUSTOM LOGIN CSS --- */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; 
            background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 75%);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box { width: 400px; padding: 20px; }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .card-header {
            background: transparent;
            border-bottom: none; /* Hilangkan garis bawah header */
            padding-top: 40px;
            padding-bottom: 10px;
        }

        /* Styling Logo agar presisi */
        .login-logo img {
            width: 100px; /* Ukuran Logo */
            height: 100px;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.3)); /* Efek glow biru tipis */
            transition: transform 0.3s;
        }
        .login-logo img:hover {
            transform: scale(1.05); /* Efek zoom dikit pas dihover */
        }

        .card-body { padding: 30px; }

        /* Input */
        .form-control {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            height: 45px;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.9);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            color: #fff;
        }
        .input-group-text {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: none;
            color: #94a3b8;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Tombol */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            height: 45px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
            transition: 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        a { color: #60a5fa; }
        .icheck-primary label { color: #cbd5e1; }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
    </style>
</head>

<body class="hold-transition login-page">
    
    <div class="login-box">
        <div class="card">
            <div class="card-header text-center">
                <div class="login-logo">
                    <a href="../index.php">
                        <img src="../assets/img/logo1.png" alt="Logo BugTracker">
                    </a>
                </div>
                <p class="text-white font-weight-bold mt-3 mb-1" style="font-size: 1.2rem;">BugTracker</p>
                <p class="login-box-msg p-0 text-muted">LOGIN</p>
            </div>
            
            <div class="card-body">
                
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger text-center rounded-lg mb-4 p-2">
                        <small><i class="fas fa-times-circle mr-1"></i> Email atau Password salah!</small>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Ingat Saya</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="login" class="btn btn-primary btn-block">
                                MASUK
                            </button>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
    </div>

    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dist/js/adminlte.min.js"></script>
</body>
</html>
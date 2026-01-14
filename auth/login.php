<?php
session_start();
require '../function.php';

if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];
    $result = mysqli_query($conn, "SELECT email FROM users WHERE user_id = $id");
    $row = mysqli_fetch_assoc($result);
    if ($key === hash('sha256', $row['email'])) {
        $_SESSION['login'] = true;
    }
}

if (isset($_SESSION['login'])) {
    if (isset($_SESSION['login']['role']) && $_SESSION['login']['role'] == 'admin') {
        header("Location: ../admin/index.php");
    } elseif (isset($_SESSION['login']['role']) && $_SESSION['login']['role'] == 'developer') {
        header("Location: ../developer/index.php");
    } else {
        header("Location: ../dashboard.php");
    }
    exit;
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) { 
            $_SESSION['login'] = [
                'user_id' => $row['user_id'],
                'name'    => $row['name'],
                'role'    => $row['role'],
                'email'   => $row['email']
            ];

            if (isset($_POST['remember'])) {
                setcookie('id', $row['user_id'], time() + 60);
                setcookie('key', hash('sha256', $row['email']), time() + 60);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TrimHub ID</title>
    
    <link rel="stylesheet" href="../assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #007bff 0%, #00d2ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative; /* Penting buat footer */
        }
        .card-login {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            background: white;
        }
        .card-header {
            background: white;
            border-bottom: none;
            padding-top: 30px;
            text-align: center;
        }
        .brand-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 50px;
            padding: 20px 20px;
            background: #f8f9fa;
            border: 1px solid #eee;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
            background: white;
        }
        .btn-login {
            border-radius: 50px;
            padding: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            background: linear-gradient(to right, #007bff, #0062cc);
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        .back-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
            transition: 0.3s;
        }
        .back-link:hover {
            color: white;
            text-decoration: none;
        }
        /* Style Footer Baru */
        .login-footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
        }
        .login-footer a {
            color: rgba(255,255,255,0.9);
            font-weight: bold;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="card card-login">
                    <div class="card-header">
                        <div class="brand-icon">
                            <img src="../assets/img/logotrimhub.png" alt="BugTracker" width="80px; height:auto;">
                        </div>
                        <h4 class="font-weight-bold text-dark">BugTracker</h4>
                        <p class="text-muted small">Silakan login untuk melanjutkan.</p>
                    </div>
                    
                    <div class="card-body px-4 pb-4">
                        
                        <?php if (isset($error)) : ?>
                            <div class="alert alert-danger text-center py-2" role="alert">
                                <small><i class="fas fa-exclamation-circle"></i> Email / Password Salah!</small>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            
                            <div class="form-group mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Email Address" required autocomplete="off">
                            </div>

                            <div class="form-group mb-4">
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                            </div>

                            <div class="form-group d-flex justify-content-between align-items-center mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                    <label class="custom-control-label small text-muted" for="remember">Ingat Saya</label>
                                </div>
                                <a href="#" class="small text-primary">Lupa Password?</a>
                            </div>

                            <button class="btn btn-primary btn-block btn-login" type="submit" name="login">
                                MASUK
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="small text-muted">Belum punya akun?</span>
                            <a href="sign-up.php" class="small font-weight-bold text-primary">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="../index.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="login-footer">
        &copy; <?= date('Y'); ?> <b>TrimHub ID</b> &bull; BugTracker<br>
    </div>

</body>
</html>
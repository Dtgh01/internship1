<?php
require '../function.php';

if (isset($_POST['register'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // 1. Cek Email udah ada belum?
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_fetch_assoc($cek)) {
        echo "<script>alert('Email sudah terdaftar! Silakan login.');</script>";
    } 
    // 2. Cek Password match gak?
    elseif ($password !== $confirm) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
    } 
    else {
        // 3. Enkripsi & Simpan
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Default Role = 'user'
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_hash', 'user')";
        mysqli_query($conn, $query);

        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Login.');
                    document.location.href = 'login.php';
                  </script>";
        } else {
            echo "<script>alert('Registrasi Gagal!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | TrimHub ID</title>
    
    <link rel="stylesheet" href="../assets/plugins/bootstrap4/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #007bff 0%, #00d2ff 100%); /* Sama kayak Login */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-register {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .form-control {
            border-radius: 50px;
            padding: 20px 20px;
            background: #f8f9fa;
            border: 1px solid #eee;
        }
        .form-control:focus {
            box-shadow: none; border-color: #007bff; background: white;
        }
        .btn-register {
            border-radius: 50px;
            padding: 10px;
            font-weight: bold;
            background: linear-gradient(to right, #007bff, #0062cc);
            border: none;
            color: white;
            transition: 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="card card-register">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h4 class="font-weight-bold text-primary">Buat Akun Baru</h4>
                            <p class="text-muted small">Gabung TrimHub dan mulai lapor bug.</p>
                        </div>

                        <form action="" method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Nama Lengkap" required>
                            </div>

                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Alamat Email" required>
                            </div>

                            <div class="form-group row">
                                <div class="col-6">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>
                                <div class="col-6">
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi Pass" required>
                                </div>
                            </div>

                            <button class="btn btn-register btn-block mt-4" type="submit" name="register">
                                DAFTAR SEKARANG
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="small text-muted">Sudah punya akun?</span>
                            <a href="login.php" class="small font-weight-bold text-primary">Login di sini</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
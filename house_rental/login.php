<?php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Kata laluan salah!";
            }
        } else {
            $error = "Emel tidak ditemui!";
        }
    } else {
        $error = "Sila isi semua ruang.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Masuk - RentEasy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }
        .logo-icon {
            font-size: 32px;
            color: #e00b25;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .genz-vibe {
            background: #fff0f3;
            color: #e00b25;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 18px;
            display: inline-block;
        }
        h2 {
            font-size: 18px;
            color: #222;
            margin-bottom: 20px;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
            position: relative;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #e00b25;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 16px;
            color: #6c757d;
            user-select: none;
        }
        .btn-primary-custom {
            width: 100%;
            background-color: #e00b25;
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }
        .btn-primary-custom:hover {
            background-color: #c50920;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6c757d;
            margin: 20px 0;
            font-size: 14px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider::before { margin-right: .75em; }
        .divider::after { margin-left: .75em; }
        .switch-link {
            font-size: 14px;
            color: #6c757d;
            margin-top: 15px;
        }
        .switch-link a {
            color: #e00b25;
            text-decoration: none;
            font-weight: bold;
        }
        .switch-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="modal-card">
    <div class="logo-icon">🏡 RentEasy</div>
    <div class="genz-vibe">✨ Student baru UPTM ke? Meh sini kitorang tolong cari rumah!</div>
    <h2>Log Masuk Akaun Anda</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Alamat emel" required autocomplete="off">
        </div>
        <div class="form-group">
            <input type="password" name="password" id="login_password" class="form-control" placeholder="Kata laluan" required>
            <span class="toggle-password" onclick="toggleLoginPassword()">👁️️</span>
        </div>
        <button type="submit" class="btn-primary-custom">Log Masuk</button>
    </form>

    <div class="divider">Atau</div>

    <div class="switch-link">
        Belum ada akaun? <a href="register.php">Daftar sekarang</a>
    </div>
    
    <div class="switch-link">
        Adakah anda Tuan Rumah? <a href="register.php?role=landlord">Daftar sebagai Landlord</a>
    </div>
</div>

<script>
function toggleLoginPassword() {
    const passwordInput = document.getElementById('login_password');
    const toggleIcon = document.querySelector('.toggle-password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
    }
}
</script>

</body>
</html>
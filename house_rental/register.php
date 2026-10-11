<?php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

$selected_role = isset($_GET['role']) && $_GET['role'] === 'landlord' ? 'landlord' : 'tenant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "Sila lengkapkan semua maklumat.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Alamat emel tidak lengkap atau tidak sah. Sila masukkan emel yang betul.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Emel ini telah berdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $role);

            if ($stmt->execute()) {
                $success = "Pendaftaran berjaya! Sila <a href='login.php'>Log Masuk</a>.";
            } else {
                $error = "Ralat sistem. Sila cuba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akaun - RentEasy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
        }
        .logo-icon {
            font-size: 32px;
            color: #e00b25;
            margin-bottom: 15px;
            font-weight: bold;
        }
        h2 {
            font-size: 20px;
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
            text-align: left;
        }
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
            position: relative;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
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
        select.form-control {
            background-color: #fff;
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
    <h2>Daftar akaun baharu</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-msg"><?= $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="name" class="form-control" placeholder="Nama Penuh" required autocomplete="off">
        </div>
        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Alamat Emel (cth: nama@gmail.com)" required autocomplete="off">
        </div>
        <div class="form-group">
            <input type="password" name="password" id="password" class="form-control" placeholder="Kata Laluan" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>
        <div class="form-group">
            <input type="text" name="phone" class="form-control" placeholder="No. Telefon (cth: 0123456789)" required autocomplete="off">
        </div>
        <div class="form-group">
            <select name="role" class="form-control" required>
                <option value="tenant" <?= $selected_role === 'tenant' ? 'selected' : ''; ?>>Penyewa (Tenant / Pelajar)</option>
                <option value="landlord" <?= $selected_role === 'landlord' ? 'selected' : ''; ?>>Tuan Rumah (Landlord)</option>
            </select>
        </div>
        <button type="submit" class="btn-primary-custom">Teruskan Daftar</button>
    </form>

    <div class="divider">Atau</div>

    <div class="switch-link">
        Sudah mempunyai akaun? <a href="login.php">Log Masuk</a>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
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
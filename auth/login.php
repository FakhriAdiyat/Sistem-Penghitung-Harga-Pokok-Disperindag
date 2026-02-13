<?php if (isset($_GET['timeout'])): ?>
<p style="color:red;">Session habis, silakan login ulang.</p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">

<div class="login-container">
    <img src="../assets/img/logo1.png" class="login-logo" alt="Logo">
    <h2>Login</h2>

    <div class="login-box">
        <form action="login_process.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Masuk</button>
        </form>
    </div>
</div>

</body>
</html>

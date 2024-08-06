<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM Employee WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        
        if ($user['Role'] == 'Dean' || $user['Role'] == 'Head of Secretary') {
            $faculty_query = "SELECT FacultyID FROM Department WHERE DepartmentID = " . $user['DepartmentID'];
            $faculty_result = mysqli_query($conn, $faculty_query);
            if ($faculty_row = mysqli_fetch_assoc($faculty_result)) {
                $user['FacultyID'] = $faculty_row['FacultyID'];
            }
        }

        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="post" action="forgot_password.php">
        <button type="submit">Forgot Password?</button>
    </form>
</body>
</html>

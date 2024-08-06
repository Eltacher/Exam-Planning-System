<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $query = "SELECT * FROM Employee WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        $message = "Your password is: " . $user['password'];
    } 
    else {
        $error = "Username not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="post" action="forgot_password.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <button type="submit">Submit</button>
    </form>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
</body>
</html>

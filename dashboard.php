<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$role = $user['Role'];
$name = $user['Name'];

switch ($role) {
    case 'Assistant':
        header('Location: assistant_dashboard.php');
        break;
    case 'Secretary':
        header('Location: secretary_dashboard.php');
        break;
    case 'Head of Department':
        header('Location: head_department_dashboard.php');
        break;
    case 'Head of Secretary':
        header('Location: head_secretary_dashboard.php');
        break;
    case 'Dean':
        header('Location: dean_dashboard.php');
        break;
    default:
        echo "Unknown role!";
        exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Dean') {
    header('Location: login.php');
    exit();
}

include('db_connection.php');

$user = $_SESSION['user'];
$name = $user['Name'];
$faculty_id = $user['FacultyID'];

if (!$faculty_id) {
    die('FacultyID not set for the current user.');
}

$departments_query = "SELECT DepartmentID, Name FROM Department WHERE FacultyID = $faculty_id";
$departments_result = mysqli_query($conn, $departments_query);

$department_id = null;
$exams_result = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['department_id'])) {
    $department_id = intval($_POST['department_id']);
    $exams_query = "
        SELECT c.Name AS CourseName, e.ExamDate, e.ExamTime
        FROM Exam e
        JOIN Courses c ON e.CourseID = c.CourseID
        WHERE c.DepartmentID = $department_id
        ORDER BY e.ExamDate ASC, e.ExamTime ASC
    ";
    $exams_result = mysqli_query($conn, $exams_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dean Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <form method="post" action="dean_dashboard.php">
        <label for="department_id">Department:</label>
        <select name="department_id" id="department_id" required>
            <?php while ($department = mysqli_fetch_assoc($departments_result)): ?>
                <option value="<?php echo $department['DepartmentID']; ?>" <?php if ($department['DepartmentID'] == $department_id) echo 'selected'; ?>><?php echo htmlspecialchars($department['Name']); ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">View Exams</button>
    </form>

    <?php if ($exams_result): ?>
        <table border="1">
            <tr>
                <th>Course Name</th>
                <th>Exam Date</th>
                <th>Exam Time</th>
            </tr>
            <?php while ($exam = mysqli_fetch_assoc($exams_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($exam['CourseName']); ?></td>
                    <td><?php echo htmlspecialchars($exam['ExamDate']); ?></td>
                    <td><?php echo htmlspecialchars($exam['ExamTime']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>

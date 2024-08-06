<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Head of Department') {
    header('Location: login.php');
    exit();
}

include('db_connection.php');

$user = $_SESSION['user'];
$name = $user['Name'];
$department_id = $user['DepartmentID'];

$exam_schedule_query = "
    SELECT c.Name AS CourseName, e.ExamDate, e.ExamTime
    FROM Exam e
    JOIN Courses c ON e.CourseID = c.CourseID
    WHERE c.DepartmentID = $department_id
    ORDER BY e.ExamDate ASC, e.ExamTime ASC
";
$exam_schedule_result = mysqli_query($conn, $exam_schedule_query);

$workload_query = "
    SELECT e.Name AS AssistantName, e.score 
    FROM Employee e
    WHERE e.DepartmentID = $department_id AND e.Role = 'Assistant'
";
$workload_result = mysqli_query($conn, $workload_query);

$total_scores_query = "
    SELECT SUM(e.score) as TotalScore
    FROM Employee e
    WHERE e.DepartmentID = $department_id AND e.Role = 'Assistant'
";
$total_scores_result = mysqli_query($conn, $total_scores_query);
$total_score_row = mysqli_fetch_assoc($total_scores_result);
$total_score = $total_score_row['TotalScore'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Head of Department Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <h3>Exam Schedule</h3>
    <table border="1">
        <tr>
            <th>Course Name</th>
            <th>Exam Date</th>
            <th>Exam Time</th>
        </tr>
        <?php while ($exam = mysqli_fetch_assoc($exam_schedule_result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['CourseName']); ?></td>
                <td><?php echo htmlspecialchars($exam['ExamDate']); ?></td>
                <td><?php echo htmlspecialchars($exam['ExamTime']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Assistant Workloads</h3>
    <table border="1">
        <tr>
            <th>Assistant Name</th>
            <th>Percentage of Workload</th>
        </tr>
        <?php while ($workload = mysqli_fetch_assoc($workload_result)): ?>
            <?php
                $percentage = ($total_score > 0) ? round(($workload['score'] / $total_score) * 100, 2) : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($workload['AssistantName']); ?></td>
                <td><?php echo htmlspecialchars($percentage . '%'); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

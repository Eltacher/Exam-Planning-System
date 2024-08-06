<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Assistant') {
    header('Location: login.php');
    exit();
}

include('db_connection.php');

$user = $_SESSION['user'];
$name = $user['Name'];
$employee_id = $user['EmployeeID'];
$department_id = $user['DepartmentID'];

$courses_query = "SELECT CourseID, Name, TimeSlot, Day FROM Courses WHERE DepartmentID = $department_id";
$courses_result = mysqli_query($conn, $courses_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_id = intval($_POST['course_id']);
    $course_query = "SELECT TimeSlot, Day FROM Courses WHERE CourseID = $course_id";
    $course_result = mysqli_query($conn, $course_query);
    while ($course_row = mysqli_fetch_assoc($course_result)) {
        $timeslot = $course_row['TimeSlot'];
        $day = $course_row['Day'];
        $insert_course_query = "INSERT INTO AssistantCourses (AssistantID, CourseID, TimeSlot, Day) VALUES ($employee_id, $course_id, '$timeslot', '$day')";
        mysqli_query($conn, $insert_course_query);
    }
}

// Refresh weekly plan
$weekly_plan = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refresh_plan'])) {
    $weekly_plan_query = "
        SELECT ac.TimeSlot, ac.Day, c.Name AS CourseName, 'Course' AS Type 
        FROM AssistantCourses ac 
        JOIN Courses c ON ac.CourseID = c.CourseID 
        WHERE ac.AssistantID = $employee_id
        UNION
        SELECT e.ExamTime AS TimeSlot, e.ExamDate AS Day, c.Name AS CourseName, 'Exam' AS Type 
        FROM AssistantExams ae
        JOIN Exam e ON ae.ExamID = e.ExamID 
        JOIN Courses c ON e.CourseID = c.CourseID 
        WHERE ae.AssistantID = $employee_id
        ORDER BY TimeSlot
    ";
    $weekly_plan_result = mysqli_query($conn, $weekly_plan_query);
    while ($row = mysqli_fetch_assoc($weekly_plan_result)) {
        $weekly_plan[$row['TimeSlot']][$row['Day']][] = ['name' => $row['CourseName'], 'type' => $row['Type']];
    }
}

// Fetch the assistant's assigned exams
$assigned_exams_query = "
    SELECT c.Name AS CourseName, e.ExamDate, e.ExamTime 
    FROM AssistantExams ae
    JOIN Exam e ON ae.ExamID = e.ExamID
    JOIN Courses c ON e.CourseID = c.CourseID
    WHERE ae.AssistantID = $employee_id
";
$assigned_exams_result = mysqli_query($conn, $assigned_exams_query);

$timeslots = ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00'];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assistant Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <form method="post" action="assistant_dashboard.php" style="display:inline;">
        <label for="course_id">Select Course:</label>
        <select name="course_id" id="course_id" required>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <option value="<?php echo $course['CourseID']; ?>"><?php echo htmlspecialchars($course['Name']) . ' (' . htmlspecialchars($course['TimeSlot']) . ' - ' . htmlspecialchars($course['Day']) . ')'; ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="add_course">Add Course</button>
    </form>

    <form method="post" action="assistant_dashboard.php" style="display:inline;">
        <button type="submit" name="refresh_plan">Refresh</button>
    </form>

    <table border="1">
        <tr>
            <th>Time Slot</th>
            <?php foreach ($days as $day): ?>
                <th><?php echo $day; ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($timeslots as $timeslot): ?>
            <tr>
                <td><?php echo $timeslot; ?></td>
                <?php foreach ($days as $day): ?>
                    <td>
                        <?php if (isset($weekly_plan[$timeslot][$day])): ?>
                            <?php foreach ($weekly_plan[$timeslot][$day] as $event): ?>
                                <?php echo htmlspecialchars($event['name']) . ' (' . htmlspecialchars($event['type']) . ')<br>'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Assigned Exams</h3>
    <table border="1">
        <tr>
            <th>Course Name</th>
            <th>Exam Date</th>
            <th>Exam Time</th>
        </tr>
        <?php while ($exam = mysqli_fetch_assoc($assigned_exams_result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['CourseName']); ?></td>
                <td><?php echo htmlspecialchars($exam['ExamDate']); ?></td>
                <td><?php echo htmlspecialchars($exam['ExamTime']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Head of Secretary') {
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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $department_id = intval($_POST['department_id']);
    $timeslot = mysqli_real_escape_string($conn, $_POST['timeslot']);
    $day = mysqli_real_escape_string($conn, $_POST['day']);

    $add_course_query = "INSERT INTO Courses (Name, DepartmentID, TimeSlot, Day) VALUES ('$course_name', $department_id, '$timeslot', '$day')";
    mysqli_query($conn, $add_course_query);
}


$courses_query = "SELECT CourseID, Name FROM Courses WHERE DepartmentID IN (SELECT DepartmentID FROM Department WHERE FacultyID = $faculty_id)";
$courses_result = mysqli_query($conn, $courses_query);


$selected_assistants = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_exam'])) {
    $course_id = intval($_POST['course_id']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $exam_time = mysqli_real_escape_string($conn, $_POST['exam_time']);
    $assistants_needed = intval($_POST['assistants_needed']);

    $insert_exam_query = "INSERT INTO Exam (CourseID, ExamDate, ExamTime) VALUES ($course_id, '$exam_date', '$exam_time')";
    mysqli_query($conn, $insert_exam_query);
    $exam_id = mysqli_insert_id($conn);

    $assistants_query = "
        SELECT e.EmployeeID, e.Name 
        FROM Employee e 
        WHERE e.DepartmentID IN (SELECT DepartmentID FROM Department WHERE FacultyID = $faculty_id)
        AND e.Role = 'Assistant'
        AND e.EmployeeID NOT IN (
            SELECT ac.AssistantID 
            FROM AssistantCourses ac 
            WHERE ac.TimeSlot = '$exam_time'
        )
        ORDER BY e.score ASC 
        LIMIT $assistants_needed
    ";
    $assistants_result = mysqli_query($conn, $assistants_query);

    while ($assistant = mysqli_fetch_assoc($assistants_result)) {
        $selected_assistants[] = $assistant;
        $assistant_id = $assistant['EmployeeID'];
        
        $assign_query = "INSERT INTO AssistantExams (AssistantID, ExamID) VALUES ($assistant_id, $exam_id)";
        mysqli_query($conn, $assign_query);

        $update_score_query = "UPDATE Employee SET score = score + 1 WHERE EmployeeID = $assistant_id";
        mysqli_query($conn, $update_score_query);
    }
}

$scores_query = "
    SELECT e.Name, e.score 
    FROM Employee e 
    WHERE e.DepartmentID IN (SELECT DepartmentID FROM Department WHERE FacultyID = $faculty_id)
    AND e.Role = 'Assistant'
    ORDER BY e.score ASC
";
$scores_result = mysqli_query($conn, $scores_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Head of Secretary Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <form method="post" action="head_secretary_dashboard.php">
        <label for="department_id">Select Department:</label>
        <select name="department_id" id="department_id" required>
            <?php while ($department = mysqli_fetch_assoc($departments_result)): ?>
                <option value="<?php echo $department['DepartmentID']; ?>"><?php echo htmlspecialchars($department['Name']); ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" id="course_name" required>
        <br>
        <label for="timeslot">Time Slot:</label>
        <input type="text" name="timeslot" id="timeslot" required>
        <br>
        <label for="day">Day:</label>
        <input type="text" name="day" id="day" required>
        <br>
        <button type="submit" name="add_course">Add Course</button>
    </form>

    <form method="post" action="head_secretary_dashboard.php">
        <label for="course_id">Select Course:</label>
        <select name="course_id" id="course_id" required>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <option value="<?php echo $course['CourseID']; ?>"><?php echo htmlspecialchars($course['Name']); ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label for="exam_date">Exam Date:</label>
        <input type="date" name="exam_date" id="exam_date" required>
        <br>
        <label for="exam_time">Exam Time:</label>
        <input type="time" name="exam_time" id="exam_time" required>
        <br>
        <label for="assistants_needed">Assistants Needed:</label>
        <input type="number" name="assistants_needed" id="assistants_needed" required>
        <br>
        <button type="submit" name="create_exam">Create Exam</button>
    </form>

    <h3>Selected Assistants</h3>
    <?php if (count($selected_assistants) > 0): ?>
        <ul>
            <?php foreach ($selected_assistants as $assistant): ?>
                <li><?php echo htmlspecialchars($assistant['Name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3>Assistant Scores</h3>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Score</th>
        </tr>
        <?php while ($score = mysqli_fetch_assoc($scores_result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($score['Name']); ?></td>
                <td><?php echo $score['score']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

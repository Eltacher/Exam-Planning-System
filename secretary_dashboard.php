<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 'Secretary') {
    header('Location: login.php');
    exit();
}

include('db_connection.php');

$user = $_SESSION['user'];
$name = $user['Name'];
$department_id = $user['DepartmentID'];

if (!$department_id) {
    die('DepartmentID not set for the current user.');
}

// Fetch courses for the dropdown based on the user's department
$courses_query = "SELECT CourseID, Name FROM Courses WHERE DepartmentID = $department_id";
$courses_result = mysqli_query($conn, $courses_query);

// Handle exam creation form submission
$selected_assistants = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_exam'])) {
    $course_id = intval($_POST['course_id']);
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $assistants_needed = intval($_POST['assistants_needed']);

    // Insert new exam
    $insert_exam_query = "INSERT INTO Exam (CourseID, ExamDate, ExamTime) VALUES ($course_id, '$exam_date', '$exam_time')";
    mysqli_query($conn, $insert_exam_query);
    $exam_id = mysqli_insert_id($conn);

    // Fetch the least-scored assistants in the same department who do not have an intersecting course with the exam
    $assistants_query = "
        SELECT e.EmployeeID, e.Name 
        FROM Employee e 
        WHERE e.DepartmentID = $department_id
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
        
        // Assign the assistant to the exam
        $assign_query = "INSERT INTO AssistantExams (AssistantID, ExamID) VALUES ($assistant_id, $exam_id)";
        mysqli_query($conn, $assign_query);

        // Update the assistant's score
        $update_score_query = "UPDATE Employee SET score = score + 1 WHERE EmployeeID = $assistant_id";
        mysqli_query($conn, $update_score_query);
    }
}

// Fetch the scores of all assistants in the department
$scores_query = "
    SELECT e.Name, e.score 
    FROM Employee e 
    WHERE e.DepartmentID = $department_id
    AND e.Role = 'Assistant'
    ORDER BY e.score ASC
";
$scores_result = mysqli_query($conn, $scores_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secretary Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <h3>Create Exam</h3>
    <form method="post" action="secretary_dashboard.php">
        <label for="course_id">Select Course:</label>
        <select name="course_id" id="course_id" required>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <option value="<?php echo $course['CourseID']; ?>"><?php echo htmlspecialchars($course['Name']); ?></option>
            <?php endwhile; ?>
        </select><br>

        <label for="exam_date">Exam Date:</label>
        <input type="date" name="exam_date" id="exam_date" required><br>

        <label for="exam_time">Exam Time:</label>
        <input type="time" name="exam_time" id="exam_time" required><br>

        <label for="assistants_needed">Assistants Needed:</label>
        <input type="number" name="assistants_needed" id="assistants_needed" required><br>

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

<?php
include "connection.php";

$successMsg = '';
$errorMsg = '';
$eventsFromDB = [];

// Fetch courses and instructors for dropdowns
$courses = [];
$instructors = [];

$courseResult = $conn->query("SELECT * FROM courses ORDER BY name");
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[$row['id']] = $row['name'];
    }
}

$instructorResult = $conn->query("SELECT * FROM instructors ORDER BY name");
if ($instructorResult && $instructorResult->num_rows > 0) {
    while ($row = $instructorResult->fetch_assoc()) {
        $instructors[$row['id']] = $row['name'];
    }
}

// ✅ Handle Add Appointment
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === "add") {
    $courseId    = $_POST["course_id"] ?? '';
    $instructorId= $_POST["instructor_id"] ?? '';
    $start       = $_POST["start_date"] ?? '';
    $end         = $_POST["end_date"] ?? '';
    $startTime   = $_POST["start_time"] ?? '';
    $endTime     = $_POST["end_time"] ?? '';

    if ($courseId && $instructorId && $start && $end && $startTime && $endTime) {
        $stmt = $conn->prepare(
            "INSERT INTO appointments (course_id, instructor_id, start_date, end_date, start_time, end_time) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iissss", $courseId, $instructorId, $start, $end, $startTime, $endTime);
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=1");
            exit;
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=1");
            exit;
        }
        $stmt->close();
    } else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?error=1");
        exit;
    }
}

// ✏️ Handle Edit Appointment
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === "edit") {
    $id          = $_POST["event_id"] ?? null;
    $courseId    = $_POST["course_id"] ?? '';
    $instructorId= $_POST["instructor_id"] ?? '';
    $start       = $_POST["start_date"] ?? '';
    $end         = $_POST["end_date"] ?? '';
    $startTime   = $_POST["start_time"] ?? '';
    $endTime     = $_POST["end_time"] ?? '';

    if ($id && $courseId && $instructorId && $start && $end && $startTime && $endTime) {
        $stmt = $conn->prepare(
            "UPDATE appointments SET course_id = ?, instructor_id = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ? 
             WHERE id = ?"
        );
        $stmt->bind_param("iissssi", $courseId, $instructorId, $start, $end, $startTime, $endTime, $id);
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=2");
            exit;
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=2");
            exit;
        }
        $stmt->close();
    } else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?error=2");
        exit;
    }
}

// 🗑️ Handle Delete Appointment
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === "delete") {
    $id = $_POST["event_id"] ?? null;

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER["PHP_SELF"] . "?success=3");
            exit;
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?error=3");
            exit;
        }
        $stmt->close();
    }
}

// ✅ Success & Error Messages
if (isset($_GET["success"])) {
    $successMsg = match ($_GET["success"]) {
        '1' => "✅ Appointment added successfully",
        '2' => "✅ Appointment updated successfully",
        '3' => "🗑️ Appointment deleted successfully",
        default => ''
    };
}

if (isset($_GET["error"])) {
    $errorMsg = match ($_GET["error"]) {
        '1' => "❗ Error adding appointment. Please check your input.",
        '2' => "❗ Error updating appointment. Please check your input.",
        '3' => "❗ Error deleting appointment.",
        default => '❗ Error occurred. Please check your input.'
    };
}

// 📅 Fetch Appointments from DB and spread by date
$result = $conn->query("
    SELECT a.*, c.name as course_name, i.name as instructor_name 
    FROM appointments a
    JOIN courses c ON a.course_id = c.id
    JOIN instructors i ON a.instructor_id = i.id
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row["start_date"]);
        $end   = new DateTime($row["end_date"]);

        while ($start <= $end) {
            $eventsFromDB[] = [
                "id"          => $row["id"],
                "title"       => "{$row['course_name']} - {$row['instructor_name']}",
                "date"        => $start->format('Y-m-d'),
                "start"       => $row["start_date"],
                "end"         => $row["end_date"],
                "start_time"  => $row["start_time"],
                "end_time"    => $row["end_time"],
                "course_id"   => $row["course_id"],
                "instructor_id" => $row["instructor_id"]
            ];
            $start->modify('+1 day');
        }
    }
}

$conn->close();
?>
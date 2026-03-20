<?php
include "connection.php";

$successMsg = '';
$errorMsg = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? '');
    $description = trim($_POST["description"] ?? '');

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO courses (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            header("Location: manage_courses.php?success=1");
            exit;
        } else {
            $errorMsg = "Error adding course";
        }
        $stmt->close();
    } else {
        $errorMsg = "Course name is required";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Course Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>➕ Add New Course<br>Course Calendar System</h1>
    </header>

    <div class="calendar" style="max-width: 600px;">
        <!-- ✅ Success / Error Messages -->
        <?php if ($successMsg): ?>
            <div class="alert success"><?= $successMsg ?></div>
        <?php elseif ($errorMsg): ?>
            <div class="alert error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="name">Course Name:</label>
            <input type="text" name="name" id="name" required>
            
            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #CCC; border-radius: 5px;"></textarea>

            <button type="submit" style="margin-top: 1rem;">💾 Save Course</button>
        </form>

        <div style="margin-top: 1rem;">
            <a href="manage_courses.php" class="btn" style="background: #6c757d; color: white;">← Back to Courses</a>
            <a href="index.php" class="btn">← Back to Calendar</a>
        </div>
    </div>
</body>
</html>
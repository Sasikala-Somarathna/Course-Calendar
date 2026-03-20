<?php
include "connection.php";

$successMsg = '';
$errorMsg = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $phone = trim($_POST["phone"] ?? '');

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO instructors (name, email, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $phone);
        
        if ($stmt->execute()) {
            header("Location: manage_instructors.php?success=1");
            exit;
        } else {
            $errorMsg = "Error adding instructor";
        }
        $stmt->close();
    } else {
        $errorMsg = "Instructor name is required";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Instructor - Course Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>➕ Add New Instructor<br>Course Calendar System</h1>
    </header>

    <div class="calendar" style="max-width: 600px;">
        <!-- ✅ Success / Error Messages -->
        <?php if ($successMsg): ?>
            <div class="alert success"><?= $successMsg ?></div>
        <?php elseif ($errorMsg): ?>
            <div class="alert error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="name">Instructor Name:</label>
            <input type="text" name="name" id="name" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email">
            
            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone">

            <button type="submit" style="margin-top: 1rem;">💾 Save Instructor</button>
        </form>

        <div style="margin-top: 1rem;">
            <a href="manage_instructors.php" class="btn" style="background: #6c757d; color: white;">← Back to Instructors</a>
            <a href="index.php" class="btn">← Back to Calendar</a>
        </div>
    </div>
</body>
</html>
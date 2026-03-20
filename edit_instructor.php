<?php
include "connection.php";

// Get instructor data
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_instructors.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM instructors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$instructor) {
    header("Location: manage_instructors.php");
    exit;
}

$successMsg = '';
$errorMsg = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $phone = trim($_POST["phone"] ?? '');

    if ($name) {
        $stmt = $conn->prepare("UPDATE instructors SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
        
        if ($stmt->execute()) {
            header("Location: manage_instructors.php?success=2");
            exit;
        } else {
            $errorMsg = "Error updating instructor";
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
    <title>Edit Instructor - Course Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>✏️ Edit Instructor<br>Course Calendar System</h1>
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
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($instructor['name']) ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($instructor['email'] ?? '') ?>">
            
            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($instructor['phone'] ?? '') ?>">

            <button type="submit" style="margin-top: 1rem;">💾 Update Instructor</button>
        </form>

        <div style="margin-top: 1rem;">
            <a href="manage_instructors.php" class="btn" style="background: #6c757d; color: white;">← Back to Instructors</a>
            <a href="index.php" class="btn">← Back to Calendar</a>
        </div>
    </div>
</body>
</html>
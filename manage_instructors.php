<?php
include "connection.php";

$successMsg = '';
$errorMsg = '';

// Handle messages from other pages
if (isset($_GET["success"])) {
    $successMsg = match ($_GET["success"]) {
        '1' => "✅ Instructor added successfully",
        '2' => "✅ Instructor updated successfully",
        '3' => "🗑️ Instructor deleted successfully",
        default => ''
    };
}

if (isset($_GET["error"])) {
    $errorMsg = match ($_GET["error"]) {
        '1' => "❗ Error managing instructor",
        default => '❗ Error occurred'
    };
}

// Handle instructor deletion
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM instructors WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: manage_instructors.php?success=3");
        exit;
    } else {
        header("Location: manage_instructors.php?error=1");
        exit;
    }
}

// Get all instructors
$instructors = $conn->query("SELECT * FROM instructors ORDER BY name");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instructors - Course Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .manage-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.875rem;
        }
        
        .btn-edit {
            background: var(--primary);
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>👨‍🏫 Manage Instructors<br>Course Calendar System</h1>
    </header>

    <div class="manage-container">
        <!-- ✅ Success / Error Messages -->
        <?php if ($successMsg): ?>
            <div class="alert success"><?= $successMsg ?></div>
        <?php elseif ($errorMsg): ?>
            <div class="alert error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <a href="add_instructor.php" class="btn btn-add">+ Add New Instructor</a>
        <a href="index.php" class="btn" style="background: #6c757d; color: white;">← Back to Calendar</a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($instructor = $instructors->fetch_assoc()): ?>
                    <tr>
                        <td><?= $instructor['id'] ?></td>
                        <td><?= htmlspecialchars($instructor['name']) ?></td>
                        <td><?= htmlspecialchars($instructor['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($instructor['phone'] ?? 'N/A') ?></td>
                        <td><?= date('M j, Y', strtotime($instructor['created_at'])) ?></td>
                        <td class="actions">
                            <a href="edit_instructor.php?id=<?= $instructor['id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="manage_instructors.php?delete_id=<?= $instructor['id'] ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this instructor?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
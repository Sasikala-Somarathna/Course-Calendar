<?php
include "calendar.php";

// Get all upcoming appointments for sidebar
include "connection.php";
date_default_timezone_set("Asia/Colombo");
$today = date('Y-m-d');

$upcomingAppointments = $conn->query("
    SELECT a.*, c.name as course_name, i.name as instructor_name 
    FROM appointments a
    JOIN courses c ON a.course_id = c.id
    JOIN instructors i ON a.instructor_id = i.id
    WHERE a.end_date >= '$today'
    ORDER BY a.start_date, a.start_time
    LIMIT 10
");

$appointmentsList = [];
if ($upcomingAppointments && $upcomingAppointments->num_rows > 0) {
    while ($row = $upcomingAppointments->fetch_assoc()) {
        $appointmentsList[] = $row;
    }
}

// Get appointment statistics
$todayAppointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE end_date = '$today'")->fetch_assoc()['count'];
$totalAppointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$todayAppointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE start_date <= '$today' AND end_date >= '$today'")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Calendar Project</title>
  <meta name="description" content="My Own Calendar Project">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <header>
    <h1>🗓️ Course Calendar<br> My Calendar Project</h1>
  </header>

  <!-- 🔧 Customization Panel Links -->
  <div style="text-align: center; margin: 1rem;">
    <a href="manage_courses.php" class="btn" style="background: #28a745;">📚 Manage Courses</a>
    <a href="manage_instructors.php" class="btn" style="background: #17a2b8;">👨‍🏫 Manage Instructors</a>
  </div>

  <div class="main-container">
    <!-- 📋 Sidebar for Appointments -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h3>📋 My Appointments</h3>
        <div class="appointment-stats">
          <span class="stat">Total: <?= $totalAppointments ?></span>
          <span class="stat">Today: <?= $todayAppointments ?></span>
        </div>
      </div>
      
      <div class="appointments-list">
        <?php if (count($appointmentsList) > 0): ?>
          <?php foreach ($appointmentsList as $appointment): ?>
            <?php
            $startDate = new DateTime($appointment['start_date']);
            $endDate = new DateTime($appointment['end_date']);
            $isMultiDay = $startDate->format('Y-m-d') !== $endDate->format('Y-m-d');
            $isToday = $appointment['start_date'] <= $today && $appointment['end_date'] >= $today;
            ?>
            
            <div class="appointment-item <?= $isToday ? 'today' : '' ?>">
              <div class="appointment-header">
                <span class="course-name"><?= htmlspecialchars($appointment['course_name']) ?></span>
                <span class="date-badge">
                  <?php if ($isMultiDay): ?>
                    <?= $startDate->format('M j') ?> - <?= $endDate->format('M j') ?>
                  <?php else: ?>
                    <?= $startDate->format('M j') ?>
                  <?php endif; ?>
                </span>
              </div>
              
              <div class="instructor-name">👨‍🏫 <?= htmlspecialchars($appointment['instructor_name']) ?></div>
              
              <div class="appointment-time">
                ⏰ <?= date('g:i A', strtotime($appointment['start_time'])) ?> - 
                <?= date('g:i A', strtotime($appointment['end_time'])) ?>
              </div>
              
              <?php if ($isToday): ?>
                <div class="today-badge">Today</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-appointments">
            <p>No upcoming appointments</p>
            <p>Click on a date to add your first appointment!</p>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="sidebar-footer">
        <a href="javascript:void(0)" onclick="scrollToToday()" class="view-today-btn">
          📍 View Today's Appointments
        </a>
      </div>
    </aside>

    <!-- 🎯 Main Content Area -->
    <main class="main-content">
      <!-- ✅ Success / Error Messages -->
      <?php if ($successMsg): ?>
        <div class="alert success"><?= $successMsg ?></div>
      <?php elseif ($errorMsg): ?>
        <div class="alert error"><?= $errorMsg ?></div>
      <?php endif; ?>

      <!-- ⏰ Clock -->
      <div class="clock-container">
        <div id="clock"></div>
      </div>

      <!-- 📅 Calendar -->
      <div class="calendar">
        <div class="nav-btn-container">
          <button onclick="changeMonth(-1)" class="nav-btn">⏮️</button>
          <h2 id="monthYear" style="margin: 0"></h2>
          <button onclick="changeMonth(1)" class="nav-btn">⏭️</button>
        </div>

        <div class="calendar-grid" id="calendar"></div>
      </div>
    </main>
  </div>

  <!-- 📌 Modal -->
  <div class="modal" id="eventModal">
    <div class="modal-content">

      <!-- Dropdown Selector -->
      <div id="eventSelectorWrapper" style="display: none;">
        <label for="eventSelector"><strong>Select Event:</strong></label>
        <select id="eventSelector" onchange="handleEventSelection(this.value)">
          <option disabled selected>Choose Event...</option>
        </select>
      </div>

      <!-- 📝 Form -->
      <form method="POST" id="eventForm">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="event_id" id="eventId">
        
        <label for="courseSelect">Course:</label>
        <select name="course_id" id="courseSelect" required>
          <option value="" disabled selected>Select a course</option>
          <?php foreach ($courses as $id => $name): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="instructorSelect">Instructor:</label>
        <select name="instructor_id" id="instructorSelect" required>
          <option value="" disabled selected>Select an instructor</option>
          <?php foreach ($instructors as $id => $name): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="startDate">Start Date:</label>
        <input type="date" name="start_date" id="startDate" required>

        <label for="endDate">End Date:</label>
        <input type="date" name="end_date" id="endDate" required>

        <label for="startTime">Start Time:</label>
        <input type="time" name="start_time" id="startTime" required>

        <label for="endTime">End Time:</label>
        <input type="time" name="end_time" id="endTime" required>

        <button type="submit">💾 Save</button>
      </form>

      <!-- 🗑️ Delete -->
      <form method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="event_id" id="deleteEventId">
        <button type="submit" class="submit-btn">🗑️ Delete</button>
      </form>

      <!-- ❌ Cancel -->
      <button type="button" class="submit-btn" onclick="closeModal()" style="background:#ccc">❌ Cancel</button>
    </div>
  </div>

  <!-- 🔽 Events JSON from PHP -->
  <script>
    const events = <?= json_encode($eventsFromDB, JSON_UNESCAPED_UNICODE); ?>;
    const courses = <?= json_encode($courses, JSON_UNESCAPED_UNICODE); ?>;
    const instructors = <?= json_encode($instructors, JSON_UNESCAPED_UNICODE); ?>;
    
    // Function to scroll to today's date in calendar
    /*function scrollToToday() {
      const todayCell = document.querySelector('.day.today');
      if (todayCell) {
        todayCell.scrollIntoView({ behavior: 'smooth', block: 'center' });
        todayCell.style.boxShadow = '0 0 0 3px var(--primary)';
        setTimeout(() => {
          todayCell.style.boxShadow = '';
        }, 2000);
      }
    }*/

      function scrollToToday() {
    const today = new Date();
    currentDate = new Date(today.getFullYear(), today.getMonth(), 1);
    renderCalendar(currentDate);
    
    // Simple highlight effect after rendering
    setTimeout(() => {
        const todayCell = document.querySelector('.day.today');
        if (todayCell) {
            todayCell.style.boxShadow = '0 0 0 3px var(--primary)';
            setTimeout(() => {
                todayCell.style.boxShadow = '';
            }, 2000);
        }
    }, 50);
}
  </script>

  <script src="calendar.js"></script>

</body>

</html>


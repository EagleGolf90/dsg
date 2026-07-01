<?php
// Admin - Registrations List
// This page displays all registrations from the registrations_complete view

// Check authentication
require_once 'check_auth.php';

// Include database configuration
require_once '../includes/config.php';

// Create database connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all registrations from the registrations_complete view
$sql = "SELECT * FROM registrations_complete ORDER BY submitted_at DESC";
$result = $conn->query($sql);

// Calculate total statistics
$total_registrations = 0;
$total_attendees = 0;
$total_revenue = 0;

if ($result && $result->num_rows > 0) {
  $result->data_seek(0); // Reset pointer
  while ($row = $result->fetch_assoc()) {
    $total_registrations++;
    $total_attendees += $row['banquet_attendees'];
    $total_revenue += $row['total_cost'];
  }
  $result->data_seek(0); // Reset pointer again for display
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrations List - Admin</title>
  <link rel="stylesheet" href="../css/attendances-styles.css" />
</head>

<body>
  <div class="container">
    <h1>📋 Christmas Luncheon Registrations</h1>
    <p class="subtitle">Complete list of all registered attendees</p>

    <!-- Statistics Dashboard -->
    <div class="stats-container">
      <div class="stat-card">
        <h3>Total Registrations</h3>
        <div class="number"><?php echo $total_registrations; ?></div>
      </div>
      <div class="stat-card">
        <h3>Total Attendees</h3>
        <div class="number"><?php echo $total_attendees; ?></div>
      </div>
      <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="number">$<?php echo number_format($total_revenue, 2); ?></div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions">
      <button class="btn btn-primary" onclick="window.print()">🖨️ Print List</button>
      <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
      <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <!-- Registrations Table -->
    <div class="table-container">
      <?php if ($result && $result->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Primary Registrant</th>
              <th>Email</th>
              <th>Cell Phone</th>
              <th>Total Attendees</th>
              <th>Additional Attendees</th>
              <th>Total Cost</th>
              <th>Submitted Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td>
                  <strong>
                    <?php echo htmlspecialchars($row['primary_first_name'] . ' ' . $row['primary_last_name']); ?>
                  </strong>
                </td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['cell_phone']); ?></td>
                <td style="text-align: center;">
                  <strong><?php echo htmlspecialchars($row['banquet_attendees']); ?></strong>
                </td>
                <td>
                  <?php if ($row['additional_attendees']): ?>
                    <span class="additional-attendees">
                      <?php echo htmlspecialchars($row['additional_attendees']); ?>
                    </span>
                  <?php else: ?>
                    <span style="color: #999;">-</span>
                  <?php endif; ?>
                </td>
                <td class="cost">$<?php echo number_format($row['total_cost'], 2); ?></td>
                <td><?php echo date('M d, Y g:i A', strtotime($row['submitted_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">
          <p>📭 No registrations found yet.</p>
          <p style="margin-top: 10px; font-size: 0.9em;">Registrations will appear here once people start signing up.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
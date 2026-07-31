<?php
/**
 * Admin Dashboard
 * Main admin panel after successful login
 */

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

// Get statistics
$stats = [];

// Total registrations
$result = $conn->query("SELECT COUNT(*) as count FROM registrations_complete");
if ($result) {
  $row = $result->fetch_assoc();
  $stats['total_registrations'] = $row['count'];
}

// Total attendees
$result = $conn->query("SELECT SUM(banquet_attendees) as total FROM registrations_complete");
if ($result) {
  $row = $result->fetch_assoc();
  $stats['total_attendees'] = $row['total'] ?? 0;
}

// Total revenue
$result = $conn->query("SELECT SUM(total_cost) as total FROM registrations_complete");
if ($result) {
  $row = $result->fetch_assoc();
  $stats['total_revenue'] = $row['total'] ?? 0;
}

// Recent registrations (last 5)
$recent_registrations = [];
$result = $conn->query("SELECT primary_first_name, primary_last_name, email, banquet_attendees, submitted_at FROM registrations_complete ORDER BY submitted_at DESC LIMIT 5");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $recent_registrations[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - DSG</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      color: #333;
    }

    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 40px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      font-size: 24px;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s;
      display: inline-block;
    }

    .btn-logout {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 1px solid white;
    }

    .btn-logout:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .welcome {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .welcome h2 {
      color: #333;
      margin-bottom: 10px;
    }

    .welcome p {
      color: #666;
      line-height: 1.6;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
      transition: transform 0.3s;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-card h3 {
      color: #666;
      font-size: 14px;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .stat-card .number {
      font-size: 36px;
      font-weight: bold;
      color: #667eea;
      margin-bottom: 10px;
    }

    .stat-card .icon {
      font-size: 48px;
      margin-bottom: 10px;
    }

    .actions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .action-card {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
      transition: all 0.3s;
      text-decoration: none;
      color: #333;
    }

    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .action-card .icon {
      font-size: 48px;
      margin-bottom: 15px;
    }

    .action-card h3 {
      margin-bottom: 10px;
      color: #667eea;
    }

    .action-card p {
      color: #666;
      font-size: 14px;
    }

    .recent-section {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .recent-section h2 {
      margin-bottom: 20px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th {
      background: #f5f7fa;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      color: #666;
      font-size: 14px;
    }

    table td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    table tr:hover {
      background: #f9f9f9;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #999;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>🎯 Admin Dashboard</h1>
    <div class="header-right">
      <div class="user-info">
        <span>👤 <?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></span>
      </div>
      <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="welcome">
      <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_fullname']); ?>! 👋</h2>
      <p>Here's an overview of your Christmas Luncheon registrations and quick access to admin tools.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="icon">📋</div>
        <h3>Total Registrations</h3>
        <div class="number"><?php echo number_format($stats['total_registrations']); ?></div>
      </div>

      <div class="stat-card">
        <div class="icon">👥</div>
        <h3>Total Attendees</h3>
        <div class="number"><?php echo number_format($stats['total_attendees']); ?></div>
      </div>

      <div class="stat-card">
        <div class="icon">💰</div>
        <h3>Total Revenue</h3>
        <div class="number">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
      </div>
    </div>

    <div class="actions-grid">
      <a href="attendances_list.php" class="action-card">
        <div class="icon">📊</div>
        <h3>View All Registrations</h3>
        <p>See complete list of all attendees</p>
      </a>

      <a href="manage_users.php" class="action-card">
        <div class="icon">👨‍💼</div>
        <h3>Manage Admin Users</h3>
        <p>Add or edit admin accounts</p>
      </a>

      <a href="id_list.php" class="action-card">
        <div class="icon">🔢</div>
        <h3>Attendee ID List</h3>
        <p>View sequential IDs for all registrants and friends</p>
      </a>

      <a href="../index.html" class="action-card">
        <div class="icon">🏠</div>
        <h3>View Website</h3>
        <p>Go to public website</p>
      </a>
    </div>

    <div class="recent-section">
      <h2>📌 Recent Registrations</h2>
      <?php if (count($recent_registrations) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Attendees</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_registrations as $reg): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($reg['primary_first_name'] . ' ' . $reg['primary_last_name']); ?></strong>
                </td>
                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                <td><?php echo htmlspecialchars($reg['banquet_attendees']); ?></td>
                <td><?php echo date('M d, Y', strtotime($reg['submitted_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">No registrations yet</div>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>
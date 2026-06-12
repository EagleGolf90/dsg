<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Admin View</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px;
      min-height: 100vh;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      overflow: hidden;
    }

    header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }

    h1 {
      font-size: 2em;
      margin-bottom: 10px;
    }

    .stats {
      display: flex;
      justify-content: space-around;
      padding: 20px;
      background: #f5f5f5;
      flex-wrap: wrap;
    }

    .stat-box {
      text-align: center;
      padding: 15px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin: 10px;
      min-width: 150px;
    }

    .stat-number {
      font-size: 2em;
      font-weight: bold;
      color: #667eea;
    }

    .stat-label {
      color: #666;
      font-size: 0.9em;
    }

    .content {
      padding: 30px;
    }

    .registration-card {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      background: white;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .registration-card:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .card-id {
      background: #667eea;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: bold;
    }

    .card-date {
      color: #666;
      font-size: 0.9em;
    }

    .card-body {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }

    .info-group {
      padding: 10px;
      background: #f9f9f9;
      border-radius: 5px;
    }

    .info-label {
      font-weight: bold;
      color: #667eea;
      font-size: 0.85em;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .info-value {
      color: #333;
      font-size: 1em;
    }

    .additional-attendees {
      margin-top: 15px;
      padding: 15px;
      background: #e8f5e9;
      border-left: 4px solid #4caf50;
      border-radius: 5px;
    }

    .attendee-list {
      list-style: none;
      margin-top: 10px;
    }

    .attendee-list li {
      padding: 5px 0;
      color: #333;
    }

    .attendee-list li:before {
      content: "👤 ";
      margin-right: 5px;
    }

    .cost-highlight {
      font-size: 1.2em;
      font-weight: bold;
      color: #4caf50;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #667eea;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .back-link:hover {
      color: #764ba2;
    }

    .no-results {
      text-align: center;
      padding: 40px;
      color: #666;
      font-style: italic;
    }

    .error-message {
      background: #ffebee;
      color: #c62828;
      padding: 20px;
      border-radius: 5px;
      margin: 20px;
      border-left: 4px solid #c62828;
    }

    .refresh-btn {
      background: #667eea;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1em;
      transition: background 0.3s ease;
    }

    .refresh-btn:hover {
      background: #764ba2;
    }

    @media (max-width: 768px) {
      .stats {
        flex-direction: column;
      }

      .stat-box {
        width: 100%;
      }

      .card-body {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <header>
      <h1>🎄 Christmas Luncheon Registrations</h1>
      <p>Deaf Seniors of Georgia - December 5, 2026</p>
    </header>

    <?php
    require_once 'includes/config.php';

    try {
      $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

      if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
      }

      // Get statistics
      $stats = $conn->query("SELECT 
                COUNT(*) as total_registrations,
                SUM(banquet_attendees) as total_attendees,
                SUM(total_cost) as total_revenue
                FROM registrations")->fetch_assoc();

      $additionalCount = $conn->query("SELECT COUNT(*) as count FROM registrations_others")->fetch_assoc()['count'];

      echo '<div class="stats">';
      echo '<div class="stat-box">';
      echo '<div class="stat-number">' . $stats['total_registrations'] . '</div>';
      echo '<div class="stat-label">Total Registrations</div>';
      echo '</div>';
      echo '<div class="stat-box">';
      echo '<div class="stat-number">' . $stats['total_attendees'] . '</div>';
      echo '<div class="stat-label">Total Attendees</div>';
      echo '</div>';
      echo '<div class="stat-box">';
      echo '<div class="stat-number">$' . number_format($stats['total_revenue'], 2) . '</div>';
      echo '<div class="stat-label">Total Revenue</div>';
      echo '</div>';
      echo '<div class="stat-box">';
      echo '<div class="stat-number">' . $additionalCount . '</div>';
      echo '<div class="stat-label">Additional Attendees</div>';
      echo '</div>';
      echo '</div>';

      echo '<div class="content">';
      echo '<a href="index.html" class="back-link">← Back to Home</a>';
      echo '<button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>';

      // Get all registrations with additional attendees
      $query = "SELECT r.*, 
                      GROUP_CONCAT(CONCAT(ro.first_name, ' ', ro.last_name) ORDER BY ro.id SEPARATOR '|') as additional_names
                      FROM registrations r
                      LEFT JOIN registrations_others ro ON r.id = ro.registration_id
                      GROUP BY r.id
                      ORDER BY r.submitted_at DESC";

      $result = $conn->query($query);

      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo '<div class="registration-card">';

          echo '<div class="card-header">';
          echo '<span class="card-id">Registration #' . $row['id'] . '</span>';
          echo '<span class="card-date">📅 ' . date('F j, Y - g:i A', strtotime($row['submitted_at'])) . '</span>';
          echo '</div>';

          echo '<div class="card-body">';

          echo '<div class="info-group">';
          echo '<div class="info-label">Primary Registrant</div>';
          echo '<div class="info-value">' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</div>';
          echo '</div>';

          echo '<div class="info-group">';
          echo '<div class="info-label">Email</div>';
          echo '<div class="info-value">📧 ' . htmlspecialchars($row['email']) . '</div>';
          echo '</div>';

          echo '<div class="info-group">';
          echo '<div class="info-label">Phone</div>';
          echo '<div class="info-value">📱 ' . htmlspecialchars($row['cell_phone']) . '</div>';
          echo '</div>';

          echo '<div class="info-group">';
          echo '<div class="info-label">Total Attendees</div>';
          echo '<div class="info-value">👥 ' . $row['banquet_attendees'] . ' people</div>';
          echo '</div>';

          echo '<div class="info-group">';
          echo '<div class="info-label">Total Cost</div>';
          echo '<div class="info-value cost-highlight">$' . number_format($row['total_cost'], 2) . '</div>';
          echo '</div>';

          echo '</div>'; // End card-body
    
          if (!empty($row['additional_names'])) {
            $additionalNames = explode('|', $row['additional_names']);
            echo '<div class="additional-attendees">';
            echo '<div class="info-label">Additional Attendees (' . count($additionalNames) . ')</div>';
            echo '<ul class="attendee-list">';
            foreach ($additionalNames as $name) {
              echo '<li>' . htmlspecialchars($name) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
          }

          echo '</div>'; // End registration-card
        }
      } else {
        echo '<div class="no-results">No registrations yet. Be the first to register!</div>';
      }

      echo '</div>'; // End content
    
      $conn->close();

    } catch (Exception $e) {
      echo '<div class="error-message">';
      echo '<strong>Error:</strong> ' . $e->getMessage();
      echo '<br><br>Please ensure:';
      echo '<ul>';
      echo '<li>Database connection settings are correct in includes/config.php</li>';
      echo '<li>Database tables are created (run database_setup.sql)</li>';
      echo '<li>Database server is accessible</li>';
      echo '</ul>';
      echo '</div>';
    }
    ?>
  </div>
</body>

</html>
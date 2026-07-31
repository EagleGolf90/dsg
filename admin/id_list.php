<?php
/**
 * ID List - Sequential unique IDs for each registrant and their friends
 */

require_once 'check_auth.php';
require_once '../includes/config.php';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all people (primary registrants + friends) ordered by registration date,
// primary registrant first within each group.
$sql = "
  SELECT reg_id, first_name, last_name, submitted_at, sort_order
  FROM (
    SELECT r.id AS reg_id, r.first_name, r.last_name, r.submitted_at, 0 AS sort_order
    FROM registrations r
    UNION ALL
    SELECT ro.registration_id AS reg_id, ro.first_name, ro.last_name, r.submitted_at, ro.id AS sort_order
    FROM registrations_others ro
    JOIN registrations r ON ro.registration_id = r.id
  ) combined
  ORDER BY submitted_at ASC, reg_id ASC, sort_order ASC
";

$result = $conn->query($sql);

$people = [];
$seq_id = 1;
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $people[] = [
      'seq_id'       => $seq_id++,
      'reg_id'       => $row['reg_id'],
      'first_name'   => $row['first_name'],
      'last_name'    => $row['last_name'],
      'submitted_at' => $row['submitted_at'],
      'is_primary'   => ($row['sort_order'] == 0),
    ];
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ID List - DSG Admin</title>
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
      gap: 15px;
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

    .btn-back {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 1px solid white;
    }

    .btn-back:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .btn-logout {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 1px solid white;
    }

    .btn-logout:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .btn-print {
      background: white;
      color: #667eea;
      font-weight: 600;
    }

    .btn-print:hover {
      background: #f0f0f0;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .page-header {
      background: white;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .page-header div h2 {
      color: #333;
      margin-bottom: 4px;
    }

    .page-header div p {
      color: #888;
      font-size: 14px;
    }

    .summary-bar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px 25px;
      border-radius: 10px;
      margin-bottom: 25px;
      display: flex;
      gap: 40px;
      font-size: 15px;
    }

    .summary-bar span strong {
      font-size: 20px;
    }

    .table-wrapper {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table thead {
      background: #f5f7fa;
    }

    table th {
      padding: 14px 16px;
      text-align: left;
      font-weight: 600;
      color: #555;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e0e0e0;
    }

    table td {
      padding: 13px 16px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    /* Alternate group shading */
    tr.group-even {
      background: #fafafa;
    }

    tr.group-odd {
      background: white;
    }

    tr:last-child td {
      border-bottom: none;
    }

    .seq-id {
      font-size: 18px;
      font-weight: 700;
      color: #667eea;
      text-align: center;
    }

    .name-cell strong {
      font-size: 15px;
    }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .badge-primary {
      background: #e8ecff;
      color: #667eea;
    }

    .badge-friend {
      background: #fff0e8;
      color: #e07030;
    }

    .reg-num {
      color: #999;
      font-size: 13px;
    }

    .no-data {
      text-align: center;
      padding: 60px;
      color: #aaa;
      font-size: 16px;
    }

    @media print {
      body { background: white; }
      .header, .page-header .actions, .btn-print, .btn-back, .btn-logout { display: none !important; }
      .container { margin: 0; max-width: 100%; }
      .table-wrapper { box-shadow: none; }
      .page-header { box-shadow: none; border: 1px solid #ddd; }
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>ID List</h1>
    <div class="header-right">
      <button onclick="window.print()" class="btn btn-print">Print</button>
      <a href="dashboard.php" class="btn btn-back">← Dashboard</a>
      <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="page-header">
      <div>
        <h2>Attendee ID List</h2>
        <p>Sequential IDs assigned to each registrant and their friends, in registration order</p>
      </div>
    </div>

    <?php if (count($people) > 0): ?>
      <div class="summary-bar">
        <span>Total People: <strong><?php echo count($people); ?></strong></span>
        <span>Last ID: <strong>#<?php echo count($people); ?></strong></span>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th style="text-align:center; width:70px;">ID #</th>
              <th>Name</th>
              <th style="width:100px;">Type</th>
              <th style="width:120px;">Reg #</th>
              <th style="width:140px;">Registered On</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $group_parity = [];
            $group_toggle = 0;
            foreach ($people as $person):
              $reg_id = $person['reg_id'];
              if (!isset($group_parity[$reg_id])) {
                $group_parity[$reg_id] = ($group_toggle % 2 === 0) ? 'even' : 'odd';
                $group_toggle++;
              }
              $row_class = 'group-' . $group_parity[$reg_id];
            ?>
              <tr class="<?php echo $row_class; ?>">
                <td class="seq-id"><?php echo $person['seq_id']; ?></td>
                <td class="name-cell">
                  <strong><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></strong>
                </td>
                <td>
                  <?php if ($person['is_primary']): ?>
                    <span class="badge badge-primary">Primary</span>
                  <?php else: ?>
                    <span class="badge badge-friend">Friend</span>
                  <?php endif; ?>
                </td>
                <td class="reg-num">Reg #<?php echo $person['reg_id']; ?></td>
                <td style="color:#777; font-size:13px;">
                  <?php echo date('M d, Y', strtotime($person['submitted_at'])); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="table-wrapper">
        <div class="no-data">No registrations found yet.</div>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>

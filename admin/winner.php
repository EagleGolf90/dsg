<?php
/**
 * Pick a random sequential attendee ID.
 */
require_once 'check_auth.php';
require_once '../includes/config.php';
require_once 'attendee_ids.php';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$result = fetch_attendee_ids($conn);

// Build the attendee list in the same sequential ID order as the Attendee ID List page.
$people = [];
$seq_id = 1;
while ($row = $result->fetch_assoc()) {
  $people[$seq_id++] = [
    'first_name' => $row['first_name'],
    'last_name'  => $row['last_name'],
  ];
}
$result->free();
$conn->close();

$max_id = count($people);

$winner = null;
$winner_name = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $max_id > 0) {
  $winner = random_int(1, $max_id);
  $winner_name = $people[$winner]['first_name'] . ' ' . $people[$winner]['last_name'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pick a Winner - DSG Admin</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      color: #333;
    }
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
    }
    .header h1 { margin: 0; font-size: 24px; }
    .back {
      color: white;
      border: 1px solid white;
      border-radius: 5px;
      padding: 10px 20px;
      text-decoration: none;
    }
    main {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
      text-align: center;
    }
    .winner-card {
      background: white;
      border-radius: 10px;
      padding: 30px 20px 40px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    h2 { margin: 0 0 10px; }
    .range, .note { color: #555; }
    .number {
      color: #4338a5;
      font-size: clamp(6rem, 24vw, 18rem);
      font-weight: 800;
      line-height: 1.1;
      overflow-wrap: anywhere;
      margin: 20px 0 10px;
    }
    .winner-name {
      color: #333;
      font-size: clamp(1.5rem, 6vw, 3rem);
      font-weight: 700;
      overflow-wrap: anywhere;
      margin: 0 0 30px;
    }
    button {
      background: #5146b8;
      color: white;
      border: 0;
      border-radius: 5px;
      padding: 16px 30px;
      font-size: 20px;
      font-weight: 600;
      cursor: pointer;
    }
    button:hover { background: #403695; }
    button:disabled { background: #777; cursor: not-allowed; }
    @media (max-width: 600px) {
      .header { padding: 20px; flex-wrap: wrap; }
      main { margin: 20px auto; }
    }
  </style>
</head>

<body>
  <header class="header">
    <h1>Pick a Winner</h1>
    <a class="back" href="id_list.php">Attendee ID List</a>
  </header>
  <main>
    <div class="winner-card">
      <?php if ($max_id > 0): ?>
        <h2>Winning Attendee ID</h2>
        <p class="range">Drawing from ID #1 to #<?php echo $max_id; ?></p>
        <div class="number" aria-live="polite"><?php echo $winner === null ? '&mdash;' : $winner; ?></div>
        <div class="winner-name" aria-live="polite"><?php echo $winner_name === null ? '' : htmlspecialchars($winner_name); ?></div>
        <form method="post">
          <button type="submit"><?php echo $winner === null ? 'Pick a Winner' : 'Pick Again'; ?></button>
        </form>
        <p class="note">Each draw includes all current attendee IDs, so a number may be picked more than once. IDs can change if registrations change; check the current attendee list.</p>
      <?php else: ?>
        <h2>No attendee IDs available</h2>
        <p class="range">Add registrations before picking a winner.</p>
        <button type="button" disabled>Pick a Winner</button>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>

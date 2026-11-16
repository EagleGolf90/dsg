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
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      /* Christmas evening sky: deep green into festive red */
      background: linear-gradient(180deg, #0b3d2e 0%, #14532d 45%, #7f1d1d 100%);
      background-attachment: fixed;
      color: #333;
    }
    .header {
      background: linear-gradient(135deg, #b91c1c 0%, #166534 100%);
      color: #fff;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      position: relative;
      z-index: 1;
    }
    .header h1 { margin: 0; font-size: 24px; }
    .header h1::before { content: "🎄 "; }
    .back {
      color: #fff;
      border: 1px solid #fff;
      border-radius: 5px;
      padding: 10px 20px;
      text-decoration: none;
    }
    /* Falling snow */
    .snow {
      position: fixed;
      inset: 0;
      overflow: hidden;
      pointer-events: none;
      z-index: 0;
    }
    .snow span {
      position: absolute;
      top: -10vh;
      color: #fff;
      opacity: 0.85;
      text-shadow: 0 0 6px rgba(255, 255, 255, 0.6);
      animation: snowfall linear infinite;
    }
    @keyframes snowfall {
      0%   { transform: translateY(-10vh) translateX(0); }
      100% { transform: translateY(110vh) translateX(6vw); }
    }
    .snow span:nth-child(1)  { left: 4%;  font-size: 14px; animation-duration: 11s; animation-delay: 0s; }
    .snow span:nth-child(2)  { left: 12%; font-size: 22px; animation-duration: 14s; animation-delay: 2s; }
    .snow span:nth-child(3)  { left: 20%; font-size: 12px; animation-duration: 10s; animation-delay: 5s; }
    .snow span:nth-child(4)  { left: 28%; font-size: 18px; animation-duration: 13s; animation-delay: 1s; }
    .snow span:nth-child(5)  { left: 36%; font-size: 24px; animation-duration: 16s; animation-delay: 4s; }
    .snow span:nth-child(6)  { left: 44%; font-size: 13px; animation-duration: 9s;  animation-delay: 7s; }
    .snow span:nth-child(7)  { left: 52%; font-size: 20px; animation-duration: 12s; animation-delay: 3s; }
    .snow span:nth-child(8)  { left: 60%; font-size: 15px; animation-duration: 15s; animation-delay: 6s; }
    .snow span:nth-child(9)  { left: 68%; font-size: 23px; animation-duration: 11s; animation-delay: 1.5s; }
    .snow span:nth-child(10) { left: 76%; font-size: 12px; animation-duration: 13s; animation-delay: 8s; }
    .snow span:nth-child(11) { left: 84%; font-size: 19px; animation-duration: 10s; animation-delay: 2.5s; }
    .snow span:nth-child(12) { left: 92%; font-size: 16px; animation-duration: 14s; animation-delay: 5.5s; }
    @media (prefers-reduced-motion: reduce) {
      .snow span { animation: none; display: none; }
    }
    main {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
      text-align: center;
      position: relative;
      z-index: 1;
    }
    .winner-card {
      background: white;
      border-radius: 10px;
      border-top: 6px solid #b91c1c;
      border-bottom: 6px solid #166534;
      padding: 30px 20px 40px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    }
    h2 { margin: 0 0 10px; }
    .range, .note { color: #555; }
    .number {
      color: #b8860b;
      font-size: clamp(1.5rem, 6vw, 3rem);
      font-weight: 800;
      line-height: 1.1;
      overflow-wrap: anywhere;
      margin: 20px 0 10px;
    }
    .winner-name {
      color: #333;
      font-size: clamp(2rem, 1rem + 6vw, 5rem);
      font-weight: 700;
      line-height: 1.15;
      overflow-wrap: anywhere;
      margin: 0 0 30px;
    }
    button {
      background: #81c784;
      color: #234b27;
      border: 0;
      border-radius: 5px;
      padding: 16px 30px;
      font-size: 20px;
      font-weight: 600;
      cursor: pointer;
    }
    button:hover { background: #66bb6a; }
    button:disabled { background: #777; cursor: not-allowed; }
    @media (max-width: 600px) {
      .header { padding: 20px; flex-wrap: wrap; }
      main { margin: 20px auto; }
    }
  </style>
</head>

<body>
  <div class="snow" aria-hidden="true">
    <span>&#10052;</span><span>&#10053;</span><span>&#10054;</span><span>&#10052;</span>
    <span>&#10053;</span><span>&#10054;</span><span>&#10052;</span><span>&#10053;</span>
    <span>&#10054;</span><span>&#10052;</span><span>&#10053;</span><span>&#10054;</span>
  </div>
  <header class="header">
    <h1>Pick a Winner</h1>
    <a class="back" href="id_list.php">Attendee ID List</a>
  </header>
  <main>
    <div class="winner-card">
      <?php if ($max_id > 0): ?>
        <h2>Winning Attendee ID</h2>
        <p class="range">Drawing from ID #1 to #<?php echo $max_id; ?></p>
        <div class="winner-name" aria-live="polite"><?php echo $winner_name === null ? '' : htmlspecialchars($winner_name); ?></div>
        <div class="number" aria-live="polite"><?php echo $winner === null ? '&mdash;' : $winner; ?></div>
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

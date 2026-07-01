<?php
/**
 * Admin Users Management Page
 * Add, edit, and manage admin accounts
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

// Handle success/error messages
$message = '';
$messageType = '';

if (isset($_GET['success'])) {
  switch ($_GET['success']) {
    case 'added':
      $message = 'Admin user added successfully!';
      $messageType = 'success';
      break;
    case 'updated':
      $message = 'Admin user updated successfully!';
      $messageType = 'success';
      break;
    case 'deleted':
      $message = 'Admin user deleted successfully!';
      $messageType = 'success';
      break;
    case 'status_updated':
      $message = 'User status updated successfully!';
      $messageType = 'success';
      break;
  }
}

if (isset($_GET['error'])) {
  switch ($_GET['error']) {
    case 'duplicate':
      $message = 'Username or email already exists!';
      $messageType = 'error';
      break;
    case 'delete_self':
      $message = 'You cannot delete your own account!';
      $messageType = 'error';
      break;
    case 'invalid_id':
      $message = 'Invalid user ID!';
      $messageType = 'error';
      break;
    default:
      $message = 'An error occurred. Please try again.';
      $messageType = 'error';
      break;
  }
}

// Get all admin users
$users = [];
$result = $conn->query("SELECT id, username, email, full_name, created_at, last_login, is_active FROM admin_users ORDER BY created_at DESC");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Admin Users - DSG</title>
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

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .btn-primary:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }

    .btn-success {
      background: #28a745;
      color: white;
    }

    .btn-success:hover {
      background: #218838;
    }

    .btn-danger {
      background: #dc3545;
      color: white;
    }

    .btn-danger:hover {
      background: #c82333;
    }

    .btn-warning {
      background: #ffc107;
      color: #333;
    }

    .btn-warning:hover {
      background: #e0a800;
    }

    .btn-small {
      padding: 6px 12px;
      font-size: 12px;
    }

    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .page-header {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .page-header h2 {
      color: #333;
    }

    .message {
      padding: 15px 20px;
      border-radius: 5px;
      margin-bottom: 20px;
      animation: slideIn 0.3s ease-out;
    }

    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .users-section {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table th {
      background: #f5f7fa;
      padding: 15px 12px;
      text-align: left;
      font-weight: 600;
      color: #666;
      font-size: 14px;
      border-bottom: 2px solid #dee2e6;
    }

    table td {
      padding: 15px 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    table tr:hover {
      background: #f9f9f9;
    }

    .status-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .status-active {
      background: #d4edda;
      color: #155724;
    }

    .status-inactive {
      background: #f8d7da;
      color: #721c24;
    }

    .action-buttons {
      display: flex;
      gap: 8px;
    }

    .no-data {
      text-align: center;
      padding: 60px;
      color: #999;
      font-size: 16px;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      animation: fadeIn 0.3s;
    }

    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 10px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.3s;
    }

    @keyframes slideUp {
      from {
        transform: translateY(50px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal-header {
      margin-bottom: 20px;
    }

    .modal-header h3 {
      color: #333;
      font-size: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-weight: 600;
      font-size: 14px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #667eea;
    }

    .form-group small {
      display: block;
      margin-top: 5px;
      color: #999;
      font-size: 12px;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 30px;
    }

    .current-user {
      background: #fff3cd;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>👨‍💼 Manage Admin Users</h1>
    <div class="header-right">
      <a href="dashboard.php" class="btn btn-back">← Back to Dashboard</a>
      <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
  </div>

  <div class="container">
    <div class="page-header">
      <h2>Admin Users</h2>
      <button onclick="openAddModal()" class="btn btn-primary">➕ Add New Admin</button>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="users-section">
      <?php if (count($users) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Username</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Created</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr <?php echo ($user['username'] === $_SESSION['admin_username']) ? 'class="current-user"' : ''; ?>>
                <td>
                  <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                  <?php if ($user['username'] === $_SESSION['admin_username']): ?>
                    <span style="color: #667eea; font-size: 12px;">(You)</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                  <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                <td>
                  <div class="action-buttons">
                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                      class="btn btn-warning btn-small">
                      ✏️ Edit
                    </button>
                    <?php if ($user['username'] !== $_SESSION['admin_username']): ?>
                      <form method="POST" action="process_user.php" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to toggle this user\'s status?');">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                        <button type="submit" class="btn btn-success btn-small">
                          <?php echo $user['is_active'] ? '🔒 Deactivate' : '✅ Activate'; ?>
                        </button>
                      </form>
                      <form method="POST" action="process_user.php" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone!');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-small">🗑️ Delete</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">
          <p>No admin users found.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add User Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>➕ Add New Admin User</h3>
      </div>
      <form method="POST" action="process_user.php" id="addForm">
        <input type="hidden" name="action" value="add">

        <div class="form-group">
          <label for="add_username">Username *</label>
          <input type="text" id="add_username" name="username" required pattern="[a-zA-Z0-9_]{3,50}"
            title="Username must be 3-50 characters, letters, numbers and underscores only">
          <small>3-50 characters, letters, numbers and underscores only</small>
        </div>

        <div class="form-group">
          <label for="add_email">Email *</label>
          <input type="email" id="add_email" name="email" required>
        </div>

        <div class="form-group">
          <label for="add_full_name">Full Name *</label>
          <input type="text" id="add_full_name" name="full_name" required maxlength="100">
        </div>

        <div class="form-group">
          <label for="add_password">Password *</label>
          <input type="password" id="add_password" name="password" required minlength="8">
          <small>Minimum 8 characters</small>
        </div>

        <div class="form-group">
          <label for="add_password_confirm">Confirm Password *</label>
          <input type="password" id="add_password_confirm" name="password_confirm" required minlength="8">
        </div>

        <div class="form-group">
          <label for="add_is_active">Status *</label>
          <select id="add_is_active" name="is_active" required>
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" onclick="closeAddModal()" class="btn">Cancel</button>
          <button type="submit" class="btn btn-primary">Add User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>✏️ Edit Admin User</h3>
      </div>
      <form method="POST" action="process_user.php" id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit_user_id" name="user_id">

        <div class="form-group">
          <label for="edit_username">Username *</label>
          <input type="text" id="edit_username" name="username" required pattern="[a-zA-Z0-9_]{3,50}"
            title="Username must be 3-50 characters, letters, numbers and underscores only">
          <small>3-50 characters, letters, numbers and underscores only</small>
        </div>

        <div class="form-group">
          <label for="edit_email">Email *</label>
          <input type="email" id="edit_email" name="email" required>
        </div>

        <div class="form-group">
          <label for="edit_full_name">Full Name *</label>
          <input type="text" id="edit_full_name" name="full_name" required maxlength="100">
        </div>

        <div class="form-group">
          <label for="edit_password">New Password</label>
          <input type="password" id="edit_password" name="password" minlength="8">
          <small>Leave blank to keep current password. Minimum 8 characters if changing.</small>
        </div>

        <div class="form-group">
          <label for="edit_password_confirm">Confirm New Password</label>
          <input type="password" id="edit_password_confirm" name="password_confirm" minlength="8">
        </div>

        <div class="modal-footer">
          <button type="button" onclick="closeEditModal()" class="btn">Cancel</button>
          <button type="submit" class="btn btn-primary">Update User</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Add Modal Functions
    function openAddModal() {
      document.getElementById('addModal').classList.add('active');
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.remove('active');
      document.getElementById('addForm').reset();
    }

    // Edit Modal Functions
    function openEditModal(user) {
      document.getElementById('edit_user_id').value = user.id;
      document.getElementById('edit_username').value = user.username;
      document.getElementById('edit_email').value = user.email;
      document.getElementById('edit_full_name').value = user.full_name;
      document.getElementById('edit_password').value = '';
      document.getElementById('edit_password_confirm').value = '';
      document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('active');
      document.getElementById('editForm').reset();
    }

    // Close modals on background click
    window.onclick = function (event) {
      if (event.target.classList.contains('modal')) {
        closeAddModal();
        closeEditModal();
      }
    }

    // Validate password match on add form
    document.getElementById('addForm').addEventListener('submit', function (e) {
      const password = document.getElementById('add_password').value;
      const confirm = document.getElementById('add_password_confirm').value;

      if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
      }
    });

    // Validate password match on edit form (only if passwords are entered)
    document.getElementById('editForm').addEventListener('submit', function (e) {
      const password = document.getElementById('edit_password').value;
      const confirm = document.getElementById('edit_password_confirm').value;

      if (password || confirm) {
        if (password !== confirm) {
          e.preventDefault();
          alert('Passwords do not match!');
          return false;
        }
      }
    });

    // Auto-hide success messages after 5 seconds
    setTimeout(function () {
      const messages = document.querySelectorAll('.message.success');
      messages.forEach(function (msg) {
        msg.style.transition = 'opacity 0.5s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
      });
    }, 5000);
  </script>
</body>

</html>
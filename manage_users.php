<?php
session_start();
include 'db_connect.php';

// Restrict access to Admin only
if (!isset($_SESSION['userID']) || $_SESSION['levelID'] != 1001) {
    header("Location: login.php");
    exit;
}

// Session timeout (15 minutes)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session expired. Please login again.');window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();

// Handle delete user
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE userID = :id");
    $stmt->bindParam(':id', $deleteID);
    if ($stmt->execute()) {
        echo "<script>alert('✅ User deleted successfully.');window.location.href='manage_users.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to delete user.');</script>";
    }
}

// Handle search and sort
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'userName';
$order = $_GET['order'] ?? 'ASC';

// Whitelist allowed columns for sorting
$allowedSort = ['userName', 'levelID', 'deptName'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'userName';
}
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

// Build query with search filter
$query = "
    SELECT users.*, department.deptName
    FROM users
    LEFT JOIN department ON users.deptID = department.deptID
    WHERE users.userName LIKE :search OR users.userIC LIKE :search
    ORDER BY $sort $order
";
$stmt = $pdo->prepare($query);
$searchParam = '%' . $search . '%';
$stmt->bindParam(':search', $searchParam);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Toggle sort order for next click
$nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - ICT Aset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; cursor: pointer; }
        th a { text-decoration: none; color: black; display: block; }
        a.button {
            background-color: #007BFF;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 5px;
        }
        a.button:hover { background-color: #0056b3; }
        .search-bar {
            margin-bottom: 15px;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            width: 250px;
        }
        .search-bar button {
            padding: 8px 12px;
        }
    </style>
</head>
<body>
    <h2>Manage Registered Users</h2>
    <a href="admin_dashboard.php">⬅️ Back to Dashboard</a> | 
    <a href="logout.php">Logout</a> | 
    <a href="register_user.php" class="button">➕ Register New User</a>
    <hr>

    <!-- 🔍 Search Form -->
    <form class="search-bar" method="GET" action="">
        <input type="text" name="search" placeholder="Search by name or IC..." value="<?= htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <?php if ($search): ?>
            <a href="manage_users.php" class="button" style="background-color: #6c757d;">Clear</a>
        <?php endif; ?>
    </form>

    <!-- 🧾 Users Table -->
    <table>
        <tr>
            <th><a href="?sort=userIC&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">User IC</a></th>
            <th><a href="?sort=userName&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Full Name</a></th>
            <th><a href="?sort=levelID&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Role</a></th>
            <th><a href="?sort=deptName&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Department</a></th>
            <th>Action</th>
        </tr>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['userIC']); ?></td>
                <td><?= htmlspecialchars($u['userName']); ?></td>
                <td><?= ($u['levelID'] == 1001) ? 'Admin' : 'Urusetia'; ?></td>
                <td><?= $u['deptName'] ? htmlspecialchars($u['deptName']) : '-'; ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $u['userID']; ?>" class="button" style="background-color: #28a745;">Edit</a>
                    <a href="manage_users.php?delete=<?= $u['userID']; ?>" class="button" style="background-color: #dc3545;" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>

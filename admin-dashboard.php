<?php
session_start();
include('db_connect.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['levelName']) || $_SESSION['levelName'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch all departments
$sql = "SELECT * FROM department";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Department Selection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #0b1b3f;
    color: white;
    font-family: 'Poppins', sans-serif;
}
.container {
    margin-top: 80px;
    background-color: #112b60;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(255,255,255,0.1);
}
.btn-dept {
    width: 100%;
    background-color: #1e3a8a;
    color: white;
    border: none;
    padding: 15px;
    border-radius: 10px;
    font-size: 18px;
    transition: all 0.3s ease;
}
.btn-dept:hover {
    background-color: #2563eb;
    transform: scale(1.03);
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #ffffff;
}
.logout-btn {
    position: absolute;
    right: 30px;
    top: 30px;
    background-color: #ef4444;
    border: none;
    padding: 10px 20px;
    color: white;
    border-radius: 8px;
    transition: 0.3s;
}
.logout-btn:hover {
    background-color: #dc2626;
}
</style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h2>Select Department to Manage Assets</h2>
    <div class="row g-3">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="col-md-6 col-lg-4">
                <form action="aset-list.php" method="GET">
                    <input type="hidden" name="deptID" value="<?php echo $row['deptID']; ?>">
                    <button type="submit" class="btn-dept">
                        <?php echo htmlspecialchars($row['deptName']); ?>
                    </button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>

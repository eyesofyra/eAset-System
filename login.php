<?php
session_start();

// If a user is already logged in, destroy the session before showing login form
if (isset($_SESSION['userID'])) {
    session_unset();
    session_destroy();
}

// Redirect if still logged in (redundant safety check)
if (isset($_SESSION['userID'])) {
    if ($_SESSION['levelID'] == 1001) {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['levelID'] == 2001) {
        header("Location: urusetia_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICT Aset System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --deep-navy: #0a1a2f;
            --gold: #d4af37;
            --navy-light: #11243d;
            --sky-blue: #e6f2ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--sky-blue);
            background: linear-gradient(135deg, #e6f2ff 0%, #cce5ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .login-header {
            background: var(--deep-navy);
            color: var(--gold);
            padding: 25px 30px;
            text-align: center;
            border-bottom: 3px solid var(--gold);
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .login-header .subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .login-body {
            padding: 30px;
        }

        .form-label {
            color: var(--deep-navy);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }

        .role-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid var(--gold);
        }

        .role-title {
            color: var(--deep-navy);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .form-check {
            margin-bottom: 10px;
            padding-left: 0;
        }

        .form-check-input {
            margin-right: 10px;
            margin-top: 0.3em;
        }

        .form-check-input:checked {
            background-color: var(--deep-navy);
            border-color: var(--deep-navy);
        }

        .form-check-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }

        .form-check-label {
            color: var(--deep-navy);
            font-weight: 500;
            cursor: pointer;
        }

        .btn-login {
            background: var(--deep-navy);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--navy-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            height: 60px;
            margin-bottom: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-body {
                padding: 20px;
            }
            
            .login-header {
                padding: 20px;
            }
        }

        .role-description {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 3px;
            padding-left: 26px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="logoKastam.jpg" alt="Kastam Easet Logo" class="logo">
            </div>
            <h2>ICT ASET SYSTEM</h2>
            <div class="subtitle">JKDM LTAKL</div>
        </div>

        <div class="login-body">
            <form action="auth_login.php" method="POST">
                <div class="mb-4">
                    <label for="userIC" class="form-label">User IC:</label>
                    <input type="text" class="form-control" name="userIC" id="userIC" required placeholder="Enter your IC number">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" name="password" id="password" required placeholder="Enter your password">
                </div>

                <div class="role-section">
                    <div class="role-title">Select Role:</div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" value="admin" id="admin" required>
                        <label class="form-check-label" for="admin">
                            Admin
                        </label>
                        <div class="role-description">Full system access and user management</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" value="urusetia" id="urusetia" required>
                        <label class="form-check-label" for="urusetia">
                            Urusetia
                        </label>
                        <div class="role-description">Department-specific asset management</div>
                    </div>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>

        <div class="login-footer">
            ICT Aset Management System © <?= date('Y'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

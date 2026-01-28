<?php
session_start();
include '../database_connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Get user info + user_id
$stmt = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['user_id'];

// === In-Progress Lessons ===
$progressSql = "
    SELECT l.lesson_id, l.header, COUNT(o.id) AS answered,
           (SELECT COUNT(*) FROM questions q WHERE q.lesson_id = l.lesson_id) AS total
    FROM ongoing_lesson o
    INNER JOIN lessons l ON o.lesson_id = l.lesson_id
    WHERE o.user_id = ?
    GROUP BY l.lesson_id, l.header
    ORDER BY MAX(o.id) DESC
    LIMIT 10
";
$stmt = $conn->prepare($progressSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inProgress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// === Recent Saved Scans ===
$scansSql = "
    SELECT scan_id, ip_address, result, datetime 
    FROM scan_results 
    WHERE user_id = ? 
    ORDER BY datetime DESC 
    LIMIT 6
";
$stmt = $conn->prepare($scansSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recentScans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SecuScan</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1)!important; transition: all 0.3s; }
        .continue-btn { background: #0d6efd; padding: 8px 20px; border-radius: 8px; font-weight: 500; }
        .continue-btn:hover { background: #0b5ed7; }
        .scan-card { background: #fff; border-radius: 12px; overflow: hidden; }
        .scan-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 12px; border-radius: 6px; max-height: 200px; overflow: auto; font-size: 0.85rem; }
    </style>
</head>
<body>

<?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => '../scanner/scan.php',
    'Validator' => '../validator/ollama_chat.php',
     'Learn' => '../modules/all_module.php',
    'Register' => '../register.php',
    'Login' => '../login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = 'dashboard.php';
    $links['Logout'] = '../logout.php';
}
Nav_Bar($links);
?>
<div class="container pt-5 mt-5">
    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm card-hover h-100">
                <div class="card-body text-center p-5 bg-primary text-white rounded">
                    <i class="fas fa-user-secret fa-4x mb-3"></i>
                    <h3>Welcome back!</h3>
                    <h4 class="fw-bold"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h4>
                    <p class="mt-3"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <a href="edit_profile.php" class="btn btn-light mt-3">Edit Profile</a>
                </div>
            </div>
        </div>

        <!-- In Progress Lessons -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book-open me-2"></i> In Progress Lessons</h5>
                    <a href="../modules/all_module.php" class="btn btn-light btn-sm">Browse All</a>
                </div>
                <div class="card-body">
                    <?php if ($inProgress): ?>
                        <?php foreach ($inProgress as $lesson):
                            $progress = $lesson['total'] > 0 ? round(($lesson['answered'] / $lesson['total']) * 100) : 0;
                        ?>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-primary mb-1"><?= htmlspecialchars($lesson['header']) ?></h6>
                                    <small class="text-muted"><?= $lesson['answered'] ?> / <?= $lesson['total'] ?> answered • <?= $progress ?>%</small>
                                    <div class="progress mt-2" style="height:6px;">
                                        <div class="progress-bar bg-success" style="width:<?= $progress ?>%"></div>
                                    </div>
                                </div>
                                <form action="../modules/intro.php" method="GET" class="ms-3">
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['lesson_id'] ?>">
                                    <button type="submit" class="btn continue-btn text-white">Continue →</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">No lessons in progress. <a href="../modules/all_module.php">Start learning!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Scans Section -->
    <div class="mt-4">
        <h4 class="mb-3"><i class="fas fa-history me-2 text-primary"></i> Recent Scans</h4>
        <?php if ($recentScans): ?>
            <div class="row g-4">
                <?php foreach ($recentScans as $scan): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card scan-card shadow-sm card-hover h-100">
                            <div class="card-header scan-header text-white">
                                <h6 class="mb-0"><i class="fas fa-desktop me-2"></i> <?= htmlspecialchars($scan['ip_address']) ?></h6>
                                <small><?= date('M j, Y - H:i', strtotime($scan['datetime'])) ?></small>
                            </div>
                            <div class="card-body">
                                <pre><?= htmlspecialchars(substr($scan['result'], 0, 400)) ?><?= strlen($scan['result']) > 400 ? '...' : '' ?></pre>
                                <div class="text-end mt-2">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scanModal<?= $scan['scan_id'] ?>">
                                        View Full Result
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Full Result -->
                    <div class="modal fade" id="scanModal<?= $scan['scan_id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Scan Result - <?= htmlspecialchars($scan['ip_address']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre style="background:#222;color:#0f0;padding:15px;border-radius:8px;max-height:70vh;overflow:auto;">
<?= htmlspecialchars($scan['result']) ?>
                                    </pre>
                                </div>
                                <div class="modal-footer">
                                    <small class="text-muted">Scanned on <?= date('F j, Y \a\t g:i A', strtotime($scan['datetime'])) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card text-center py-5">
                <p class="text-muted">No saved scans yet. Go to <a href="../scanner/scan.php">Scanner</a> and save your first scan!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
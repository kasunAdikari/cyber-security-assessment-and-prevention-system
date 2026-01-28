<?php
session_start();
require_once __DIR__ . '/db.php';

// Check login
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "user") {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['username']; // must be set during login
$lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
if ($lessonId <= 0) {
    die("Invalid lesson");
}

// fetch lesson
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE lesson_id=?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();
if (!$lesson) die("Lesson not found");

// fetch questions
$qStmt = $pdo->prepare("SELECT * FROM questions WHERE lesson_id=? ORDER BY id ASC");
$qStmt->execute([$lessonId]);
$questions = $qStmt->fetchAll();

$total = count($questions);
$score = null;
$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

// fetch previously saved answers
$aStmt = $pdo->prepare("
    SELECT id, answer
    FROM ongoing_lesson
    WHERE user_id = ? AND lesson_id = ?
");
$aStmt->execute([$userId, $lessonId]);
$savedAnswers = $aStmt->fetchAll(PDO::FETCH_KEY_PAIR); 
// result: [question_id => answer]

if ($submitted && $total > 0) {
    $score = 0;

    foreach ($questions as $q) {
        $name = 'q_' . $q['id'];
        $selected = isset($_POST[$name]) ? trim($_POST[$name]) : null;

        if ($selected !== null) {
            // Insert or update ongoing answer
            $save = $pdo->prepare("
                INSERT INTO ongoing_lesson (user_id, lesson_id, id, answer)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE answer = VALUES(answer)
            ");
            $save->execute([$userId, $lessonId, $q['id'], $selected]);
        }

        // check score
        if ($selected !== null && strcasecmp($selected, trim($q['correct_answer'])) === 0) {
            $score++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($lesson['header']) ?></title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <style>
    body {
      background:#0f2027;
      background:linear-gradient(to right, #2c5364, #203a43, #0f2027);
      color:white;
    }
    .container { margin-top:70px; padding:20px; background:rgba(0,0,0,0.6); border-radius:10px; }
    .score-box { background:#111; padding:10px; border-radius:5px; margin-bottom:15px; }
    .question-card { background:#12171c; border:1px solid #2b3a43; border-radius:8px; padding:15px; margin-bottom:15px; }
  </style>
</head>
<body>
  <?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => '../scanner/scan.php',
    'Validator' => '../validator/ollama_chat.php',
    'Learn' => 'all_module.php',
    'Register' => '../register.php',
    'Login' => '../login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = '../user/dashboard.php';
    $links['Logout'] = 'logout.php';
}
Nav_Bar($links);
?>
<div class="container">
  <a href="all_module.php" class="btn btn-secondary btn-sm mb-3">← Back to Modules</a>

  <h1 class="mb-3"><?= htmlspecialchars($lesson['header']) ?></h1>
  <div class="mb-4"><?= nl2br(htmlspecialchars($lesson['content'])) ?></div>

  <?php if ($total > 0): ?>
    <div class="score-box">
      <?php if ($submitted): ?>
        <h4>Your Score: <?= (int)$score ?> / <?= (int)$total ?></h4>
      <?php else: ?>
        <h4>Quiz: <?= (int)$total ?> questions</h4>
      <?php endif; ?>
    </div>

    <form method="POST">
      <?php foreach ($questions as $q): ?>
        <?php
          $opts = array_map('trim', explode(',', $q['answers']));
          $inputName = 'q_' . $q['id'];

          // user answer = from POST (if just submitted) OR DB (previous session)
          if ($submitted && isset($_POST[$inputName])) {
              $userAns = $_POST[$inputName];
          } elseif (isset($savedAnswers[$q['id']])) {
              $userAns = $savedAnswers[$q['id']];
          } else {
              $userAns = null;
          }
        ?>
        <div class="question-card">
          <p class="mb-2"><b><?= htmlspecialchars($q['questions']) ?></b></p>

          <?php foreach ($opts as $opt): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio"
                     name="<?= $inputName ?>"
                     value="<?= htmlspecialchars($opt) ?>"
                     id="<?= $inputName . '_' . md5($opt) ?>"
                     <?= ($userAns === $opt) ? 'checked' : '' ?>>
              <label class="form-check-label" for="<?= $inputName . '_' . md5($opt) ?>">
                <?= htmlspecialchars($opt) ?>
              </label>
            </div>
          <?php endforeach; ?>

          <?php if ($submitted): ?>
            <div class="mt-2">
              <?php if ($userAns && strcasecmp(trim($userAns), trim($q['correct_answer'])) === 0): ?>
                <span class="badge bg-success">Correct</span>
              <?php else: ?>
                <span class="badge bg-danger">Incorrect</span>
                <small class="ms-2">Correct: <?= htmlspecialchars($q['correct_answer']) ?></small>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn btn-success">Submit Answers</button>
    </form>
  <?php else: ?>
    <div class="alert alert-info">No quiz questions for this lesson yet.</div>
  <?php endif; ?>
</div>
</body>
</html>

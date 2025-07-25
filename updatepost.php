<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$post_id = intval($_GET['id'] ?? 0);

if (!$post_id) {
    $_SESSION['error_message'] = "Invalid post ID.";
    header("Location: dashboard.php");
    exit;
}

// CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch the existing post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Post not found or unauthorized access.";
    header("Location: dashboard.php");
    exit;
}

$post = $result->fetch_assoc();
$title = $post['title'];
$content = $post['content'];
$stmt->close();

// Update form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: dashboard.php");
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $_SESSION['error_message'] = "❗ Title and Content cannot be empty.";
    } else {
        $update_stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("ssii", $title, $content, $post_id, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "✅ Post updated successfully!";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error_message'] = "❌ Error updating post: " . $conn->error;
        }
        $update_stmt->close();
    }
}

$current_error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Post</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      color: #333;
    }

    .container {
      max-width: 800px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-title {
      font-size: 2rem;
      font-weight: bold;
      background: linear-gradient(45deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-align: center;
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border-radius: 10px;
      border: 2px solid rgba(102, 126, 234, 0.2);
      font-size: 1rem;
      background: rgba(255, 255, 255, 0.8);
    }

    .form-control:focus {
      border-color: #667eea;
      outline: none;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .action-btn {
      background: linear-gradient(45deg, #667eea, #764ba2);
      color: white;
      padding: 12px 25px;
      border-radius: 12px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .back-btn {
      background: #6c757d;
      margin-left: 10px;
    }

    .back-btn:hover {
      background: #5a6268;
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
    }

    .alert {
      border-radius: 15px;
      margin-bottom: 20px;
      padding: 15px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-danger {
      background: linear-gradient(45deg, #ff6b6b, #ee5a52);
      color: white;
    }

    .alert-success {
      background: linear-gradient(45deg, #56ab2f, #a8e6cf);
      color: white;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="form-title">Edit Blog Post</h1>

    <?php if ($current_error_message): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= $current_error_message ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="form-group">
        <label for="title" class="form-label">Post Title</label>
        <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
      </div>

      <div class="form-group">
        <label for="content" class="form-label">Content</label>
        <textarea name="content" id="content" class="form-control" required><?= htmlspecialchars($content) ?></textarea>
      </div>

      <div>
        <button type="submit" class="action-btn">
          <i class="fas fa-save"></i> Update Post
        </button>
        <a href="dashboard.php" class="action-btn back-btn">
          <i class="fas fa-arrow-left"></i> Cancel
        </a>
      </div>
    </form>
  </div>
</body>
</html>

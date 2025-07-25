<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'User';

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

$search_condition = '';
$params = [$user_id];
$types = 'i';

if ($search) {
    $search_condition = "AND (title LIKE ? OR content LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Get total post count
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE user_id = ? $search_condition";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, $types, ...$params);
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_posts = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_posts / $limit);

// Get posts for current page
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$sql = "SELECT id, title, content, created_at FROM posts WHERE user_id = ? $search_condition ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #4f46e5;
      --accent: #22d3ee;
      --bg-dark: #0f172a;
      --text-light: #f1f5f9;
      --text-muted: #94a3b8;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 2rem;
    }

    .hero h1 {
      font-size: 3rem;
      text-align: center;
      color: var(--accent);
      margin-bottom: 0.5rem;
    }

    .stats {
      text-align: center;
      font-size: 1.2rem;
      margin-bottom: 2rem;
    }

    .search-input {
      width: 100%;
      padding: 1rem;
      border-radius: 8px;
      border: none;
      margin-bottom: 1.5rem;
      font-size: 1rem;
    }

    .posts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .post-card {
      background-color: #1e293b;
      border-radius: 12px;
      padding: 1.5rem;
      transition: transform 0.2s ease;
    }

    .post-card:hover {
      transform: translateY(-5px);
    }

    .post-title {
      font-size: 1.25rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .post-content {
      color: var(--text-muted);
      font-size: 0.95rem;
      margin-bottom: 0.75rem;
    }

    .post-meta {
      font-size: 0.85rem;
      color: var(--text-muted);
    }

    .btn-create, .btn-logout, .btn-view {
      padding: 10px 16px;
      font-size: 14px;
      border-radius: 10px;
      border: none;
      color: white;
      margin-right: 10px;
    }

    .btn-create { background-color: #10b981; }
    .btn-view { background-color: #3b82f6; }
    .btn-logout { background-color: #ef4444; }

    .no-posts {
      text-align: center;
      padding: 50px 0;
      color: var(--text-muted);
    }

    .pagination .page-link {
      background-color: transparent;
      border: 1px solid var(--accent);
      color: var(--text-light);
      margin: 0 4px;
      border-radius: 6px;
    }

    .pagination .active .page-link {
      background-color: var(--accent);
      color: #0f172a;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="d-flex justify-content-end mb-4">
        <a href="blogpost.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Back</a>
      <a href="insertpost.php" class="btn btn-create"><i class="fas fa-plus"></i> New Post</a>
      <a href="displaypost.php" class="btn btn-view"><i class="fas fa-eye"></i> View Posts</a>
      <form method="POST" action="logout.php" class="d-inline">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn btn-logout" onclick="return confirm('Logout?')">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </form>
    </div>

    <div class="hero">
      <h1>Welcome To Dashboard <br> <?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></h1>
      <div class="stats">
        <i class="fas fa-chart-line"></i> <?= $total_posts ?> posts
      </div>
    </div>

    <form method="GET">
      <input type="text" name="search" class="search-input" placeholder="Search posts..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </form>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <div class="posts-grid">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="post-card">
            <h3 class="post-title"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="post-content">
              <?= nl2br(htmlspecialchars(substr($row['content'], 0, 200), ENT_QUOTES, 'UTF-8')) ?><?= strlen($row['content']) > 200 ? '...' : '' ?>
            </div>
            <div class="post-meta">
              <i class="fas fa-calendar-alt"></i>
              <?= date('F j, Y \a\t g:i A', strtotime($row['created_at'])) ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="no-posts">
        <h4>No posts found</h4>
        <p><?= $search ? "Try different search terms or " : "" ?><a href="insertpost.php" style="color: var(--accent);">create your first post</a></p>
      </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
      <nav>
        <ul class="pagination justify-content-center mt-4">
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"> <?= $i ?> </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</body>
</html>

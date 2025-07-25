<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php'; // DB connection

/* ---------- CONFIG ---------- */
$limit = 5;

/* ---------- INPUT ---------- */
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

/* ---------- HELPER FUNCTION ---------- */
function bindParams(mysqli_stmt $stmt, string $types, array $params): void {
    $refs = [];
    foreach ($params as $k => $v) $refs[$k] = &$params[$k];
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

/* ---------- SEARCH CLAUSE ---------- */
$searchClause = '';
$params = [];
$types  = '';

if ($search !== '') {
    $searchClause = 'WHERE title LIKE ? OR content LIKE ?';
    $like = "%{$search}%";
    $params = [$like, $like];
    $types  = 'ss';
}

/* ---------- COUNT POSTS ---------- */
$count_sql = "SELECT COUNT(*) AS total FROM posts $searchClause";
if (!$count_stmt = $conn->prepare($count_sql)) die("Count prep error: {$conn->error}");

if ($search !== '') bindParams($count_stmt, $types, $params);
$count_stmt->execute();
$total_posts = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$total_pages = max(1, (int)ceil($total_posts / $limit));

/* ---------- GET POSTS ---------- */
$list_sql = "
  SELECT id, title, content, created_at
  FROM posts
  $searchClause
  ORDER BY created_at DESC
  LIMIT ? OFFSET ?
";
$list_stmt = $conn->prepare($list_sql) or die("List prep error: {$conn->error}");

$params_list = $params;
$types_list  = $types . 'ii';
$params_list[] = $limit;
$params_list[] = $offset;

bindParams($list_stmt, $types_list, $params_list);
$list_stmt->execute();
$result = $list_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buduuuuu... - Stories that matter</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #a18cd1, skyblue);
      color: #f8fafc;
      font-family: 'Inter', sans-serif;
    }
    .header {
        color: black;
      text-align: center;
      margin: 2rem 0;
    }
    .header .logo {
      font-size: 3rem;
      background: white;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .nav-btns {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .search-form {
      max-width: 600px;
      margin: 0 auto 2rem;
    }
    .post-card {
      background: darkcyan;
      border-radius: 1rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .post-title {
      font-size: 1.5rem;
      color: #fff;
    }
    .post-meta {
      color: #94a3b8;
      font-size: 0.875rem;
    }
    .post-excerpt {
      color: #cbd5e1;
      margin-top: 0.5rem;
      white-space: pre-wrap;
    }
    .pagination {
      justify-content: center;
    }
    .no-posts {
      text-align: center;
      padding: 2rem;
      color: #94a3b8;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1 class="logo">Budduuuuu...</h1>
    <p>Stories that matter, voices that inspire</p>
  </div>

  <div class="nav-btns">
    <a href="dashboard.php" class="btn btn-outline-info btn-sm">Dashboard</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST" action="logout.php" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
      </form>
    <?php else: ?>
      <a href="login.php" class="btn btn-outline-success btn-sm">Sign In</a>
      <a href="register.php" class="btn btn-outline-warning btn-sm">Sign Up</a>
    <?php endif; ?>
  </div>

  <form class="search-form" method="GET">
    <input type="text" name="search" class="form-control" placeholder="Search stories..." value="<?= htmlspecialchars($search) ?>">
  </form>

  <div class="posts">
    <?php if ($result->num_rows): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="post-card">
          <h2 class="post-title"><?= htmlspecialchars($row['title']) ?></h2>
          <div class="post-meta">Posted on <?= date("F j, Y", strtotime($row['created_at'])) ?></div>
          <div class="post-excerpt"><?= nl2br(htmlspecialchars($row['content'])) ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-posts">No posts found. Try another keyword.</div>
    <?php endif; ?>
  </div>

  <?php if ($total_pages > 1): ?>
    <nav>
      <ul class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
</body>
</html>

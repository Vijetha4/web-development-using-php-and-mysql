<?php
include 'db.php';
session_start(); // Required to check session for secure delete/edit if needed

$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Blog Posts</title>
  <style>
    body {
      font-family: "Segoe UI", Tahoma, sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: auto;
    }
    .post {
      background-color: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
      position: relative;
    }
    .post h2 {
      margin-top: 0;
      font-size: 24px;
      color: #111827;
    }
    .post p {
      color: #374151;
      line-height: 1.6;
    }
    .date {
      color: #6b7280;
      font-size: 13px;
      margin-bottom: 10px;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #1f2937;
    }
    .btn-group {
      margin-top: 10px;
    }
    .btn {
      padding: 6px 12px;
      margin-right: 10px;
      border: none;
      border-radius: 5px;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-edit {
      background-color: #3b82f6;
      color: white;
    }
    .btn-delete {
      background-color: #ef4444;
      color: white;
    }
    .btn-edit:hover {
      background-color: #2563eb;
    }
    .btn-delete:hover {
      background-color: #dc2626;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>📝 All Blog Posts</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="post">
          <h2><?= htmlspecialchars($row['title']) ?></h2>
          <div class="date">Posted on <?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></div>
          <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>

          <div class="btn-group">
            <a class="btn btn-edit" href="updatepost.php?id=<?= $row['id'] ?>">Edit</a>
            <a class="btn btn-delete" href="deletepost.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No posts found.</p>
    <?php endif; ?>
  </div>
</body>
</html>

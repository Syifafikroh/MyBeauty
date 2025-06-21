<?php
// admin/dashboard.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [];

$article_count = $conn->query("SELECT COUNT(*) FROM article")->fetchColumn();
$category_count = $conn->query("SELECT COUNT(*) FROM category")->fetchColumn();
$author_count = $conn->query("SELECT COUNT(*) FROM author")->fetchColumn();

$stats = [
    'articles' => $article_count,
    'categories' => $category_count,
    'authors' => $author_count
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MyBeauty</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }

        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .admin-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .admin-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .admin-menu a:hover {
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .dashboard-content {
            padding: 2rem 0;
        }

        .dashboard-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .action-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .action-btn {
            background: #667eea;
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 0.5rem 0.5rem 0.5rem 0;
            font-weight: 500;
            transition: background 0.3s;
        }

        .action-btn:hover {
            background: #556cd6;
        }

        .action-btn.secondary {
            background: #6c757d;
        }

        .action-btn.secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-menu {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="container">
        <nav class="admin-nav">
            <div class="admin-logo">MyBeauty Admin</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_articles.php">Artikel</a></li>
                <li><a href="manage_categories.php">Kategori</a></li>
                <li><a href="manage_authors.php">Penulis</a></li>
                <li><a href="../index.php" target="_blank">Lihat Blog</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="dashboard-content">
        <h1 class="dashboard-title">Dashboard Admin</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['articles']; ?></div>
                <div class="stat-label">Total Artikel</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['categories']; ?></div>
                <div class="stat-label">Total Kategori</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['authors']; ?></div>
                <div class="stat-label">Total Penulis</div>
            </div>
        </div>

        <div class="quick-actions">
            <div class="action-card">
                <h3 class="action-title">Kelola Artikel</h3>
                <p>Tambah, edit, atau hapus artikel di blog Anda.</p>
                <a href="manage_articles.php" class="action-btn">Kelola Artikel</a>
                <a href="add_article.php" class="action-btn">Tambah Artikel Baru</a>
            </div>

            <div class="action-card">
                <h3 class="action-title">Kelola Kategori</h3>
                <p>Atur kategori untuk mengorganisir artikel Anda.</p>
                <a href="manage_categories.php" class="action-btn">Kelola Kategori</a>
                <a href="add_category.php" class="action-btn">Tambah Kategori</a>
            </div>

            <div class="action-card">
                <h3 class="action-title">Kelola Penulis</h3>
                <p>Tambah atau edit informasi penulis artikel.</p>
                <a href="manage_authors.php" class="action-btn">Kelola Penulis</a>
                <a href="add_author.php" class="action-btn">Tambah Penulis</a>
            </div>

            <div class="action-card">
                <h3 class="action-title">Lihat Blog</h3>
                <p>Lihat tampilan blog untuk pengunjung.</p>
                <a href="../index.php" target="_blank" class="action-btn secondary">Buka Blog</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

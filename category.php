<?php
// category.php - Articles by Category
if (basename($_SERVER['PHP_SELF']) == 'category.php') {
    include_once 'config.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $category_id = isset($_GET['id']) ? $_GET['id'] : 0;
    $articles = getArticlesByCategory($conn, $category_id);
    $categories = getCategories($conn);
    
    // Get category name
    $category_query = "SELECT name FROM category WHERE id = :id";
    $category_stmt = $conn->prepare($category_query);
    $category_stmt->bindParam(':id', $category_id);
    $category_stmt->execute();
    $current_category = $category_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_category) {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori: <?php echo htmlspecialchars($current_category['name']); ?> - MyBeauty</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="container">
        <nav class="nav">
            <a href="index.php" class="logo">MyBeauty</a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="index.php#tentang">Tentang</a></li>
                <li><a href="index.php#kontak">Kontak</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h1>Kategori: <?php echo htmlspecialchars($current_category['name']); ?></h1>
        <p>Artikel-artikel dalam kategori <?php echo htmlspecialchars($current_category['name']); ?></p>
    </div>
</section>

<div class="container">
    <div class="main-content">
        <main class="content">
            <?php if (empty($articles)): ?>
                <div class="article-card">
                    <h2>Belum ada artikel dalam kategori ini</h2>
                    <p>Artikel akan segera hadir!</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <?php if ($article['picture']): ?>
                            <img src="<?php echo htmlspecialchars($article['picture']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                 class="article-image">
                        <?php endif; ?>
                        
                        <div class="article-meta">
                            <?php echo date('d M Y', strtotime($article['date'])); ?>
                            | Kategori: <?php echo htmlspecialchars($article['category_name']); ?>
                        </div>
                        
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="article-title">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        
                        <div class="article-excerpt">
                            <?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?>
                        </div>
                        
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more">
                            Selengkapnya →
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <aside class="sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Pencarian</h3>
                <form action="index.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari artikel..." class="search-input">
                    <button type="submit" class="search-btn">Cari</button>
                </form>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Kategori</h3>
                <ul class="category-list">
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="category.php?id=<?php echo $category['id']; ?>" 
                               <?php echo ($category['id'] == $category_id) ? 'style="color: #9c4dcc; font-weight: bold; background: rgba(156, 77, 204, 0.2); border-left-color: #9c4dcc;"' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Tentang</h3>
                <p>MyBeauty adalah blog yang menghadirkan tips kecantikan, perawatan kulit, makeup, dan gaya hidup untuk wanita modern.</p>
            </div>
        </aside>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; 2025 MyBeauty. All rights reserved.</p>
    </div>
</footer>

</body>
</html>

<?php
}
?>
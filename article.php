<?php
// article.php - Article Detail Page
if (basename($_SERVER['PHP_SELF']) == 'article.php') 
    include_once 'config.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $article_id = isset($_GET['id']) ? $_GET['id'] : 0;
    $article = getArticleById($conn, $article_id);
    $categories = getCategories($conn);
    
    // Get related articles
    $related_query = "SELECT * FROM article WHERE id != :id ORDER BY RAND() LIMIT 5";
    $related_stmt = $conn->prepare($related_query);
    $related_stmt->bindParam(':id', $article_id);
    $related_stmt->execute();
    $related_articles = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$article) {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - MyBeauty</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for article page */
        body {
            background: linear-gradient(135deg, #f3e7f9 0%, #e8d5f2 100%);
        }

        .main-content {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr 280px;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Article Container */
        .article-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(156, 77, 204, 0.15);
            padding: 2.8rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: none;
        }

        .article-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #9c4dcc, #673ab7);
        }

        .article-title {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            color: #4a148c;
            font-weight: 700;
            line-height: 1.3;
            background: linear-gradient(45deg,rgb(127, 0, 206));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .article-meta {
            color: #9c4dcc;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(156, 77, 204, 0.2);
            font-weight: 600;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 2rem;
            filter: brightness(1.1) saturate(1.1);
            box-shadow: 0 8px 25px rgba(156, 77, 204, 0.15);
        }

        .article-content {
            line-height: 1.7;
            font-size: 1rem;
            color: #444;
            text-align: justify;
        }

        .article-content p {
            margin-bottom: 1.2rem;
            font-size: 1rem;
        }

        .article-content h1,
        .article-content h2,
        .article-content h3,
        .article-content h4,
        .article-content h5,
        .article-content h6 {
            margin-top: 1.8rem;
            margin-bottom: 1rem;
            color: #4a148c;
            font-weight: 600;
            line-height: 1.3;
        }

        .article-content h1 {
            font-size: 1.8rem;
            border-bottom: 3px solid #9c4dcc;
            padding-bottom: 0.5rem;
        }

        .article-content h2 {
            font-size: 1.5rem;
            color: #9c4dcc;
        }

        .article-content h3 {
            font-size: 1.3rem;
        }

        .article-content h4 {
            font-size: 1.1rem;
        }

        .article-content h5,
        .article-content h6 {
            font-size: 1rem;
            font-weight: 600;
        }

        .article-content ul,
        .article-content ol {
            margin-left: 2rem;
            margin-bottom: 1.2rem;
        }

        .article-content li {
            margin-bottom: 0.4rem;
            font-size: 1rem;
        }

        .article-content blockquote {
            border-left: 4px solid #9c4dcc;
            margin: 1.5rem 0;
            padding: 1rem 2rem;
            background: rgba(156, 77, 204, 0.05);
            font-style: italic;
            border-radius: 0 15px 15px 0;
            font-size: 1rem;
        }

        .article-content a {
            color: #9c4dcc;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: border-color 0.3s;
        }

        .article-content a:hover {
            border-bottom-color: #9c4dcc;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            margin: 1rem 0;
            box-shadow: 0 8px 25px rgba(156, 77, 204, 0.15);
        }

        .article-content code {
            background: rgba(156, 77, 204, 0.1);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #9c4dcc;
        }

        .article-content pre {
            background: rgba(156, 77, 204, 0.05);
            padding: 1rem;
            border-radius: 15px;
            overflow-x: auto;
            margin: 1.5rem 0;
            border-left: 4px solid #9c4dcc;
            font-size: 0.9rem;
        }

        .article-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            box-shadow: 0 8px 25px rgba(156, 77, 204, 0.15);
            border-radius: 15px;
            overflow: hidden;
            font-size: 0.95rem;
        }

        .article-content th,
        .article-content td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid rgba(156, 77, 204, 0.1);
        }

        .article-content th {
            background: #9c4dcc;
            color: white;
            font-weight: 600;
        }

        .article-content tr:hover {
            background: rgba(156, 77, 204, 0.05);
        }

        .article-content hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, #9c4dcc, #673ab7);
            margin: 2rem 0;
            border-radius: 2px;
        }

        .back-btn {
            background: linear-gradient(135deg, #9c4dcc 0%, #673ab7 100%);
            color: white;
            padding: 0.8rem 1.8rem;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(156, 77, 204, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(156, 77, 204, 0.4);
        }

        /* Sidebar - Remove Container */
        .sidebar-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(156, 77, 204, 0.15);
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .sidebar-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #9c4dcc, #673ab7);
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        /* Fix untuk Search Box */
        .search-box {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid rgba(156, 77, 204, 0.2);
            border-radius: 12px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
            background: white;
            min-width: 0; /* Penting untuk flexbox */
        }

        .search-input:focus {
            border-color: #9c4dcc;
            box-shadow: 0 0 0 3px rgba(156, 77, 204, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, #9c4dcc 0%, #673ab7 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            white-space: nowrap;
            flex-shrink: 0; /* Mencegah tombol mengecil */
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(156, 77, 204, 0.3);
        }

        .related-article {
            display: block;
            color: #666;
            text-decoration: none;
            padding: 1rem;
            border-bottom: 1px solid rgba(156, 77, 204, 0.1);
            transition: all 0.3s ease;
            font-size: 0.95rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            background: rgba(156, 77, 204, 0.05);
            border-left: 3px solid transparent;
        }

        .related-article:hover {
            color: #9c4dcc;
            background: rgba(156, 77, 204, 0.1);
            border-left-color: #9c4dcc;
            transform: translateX(5px);
        }

        @media (max-width: 768px) {
            .article-container {
                padding: 2rem;
                margin-bottom: 1.5rem;
            }

            .sidebar-section {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .article-title {
                font-size: 1.8rem;
            }

            .article-content {
                font-size: 0.95rem;
            }

            .article-content h1 {
                font-size: 1.5rem;
            }

            .article-content h2 {
                font-size: 1.3rem;
            }

            .article-content h3 {
                font-size: 1.1rem;
            }

            .article-content ul,
            .article-content ol {
                margin-left: 1.5rem;
            }

            .article-content table {
                font-size: 0.85rem;
            }

            .article-content th,
            .article-content td {
                padding: 0.6rem;
            }

            /* Mobile search box adjustments */
            .search-box {
                gap: 0.4rem;
            }

            .search-input {
                padding: 0.7rem 0.9rem;
                font-size: 0.85rem;
            }

            .search-btn {
                padding: 0.7rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <nav class="nav">
            <a href="index.php" class="logo">MyBeauty</a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#kontak">Kontak</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <main class="content">
            <a href="index.php" class="back-btn">← Kembali</a>
            
            <div class="article-container">
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div class="article-meta">
                    Ditulis pada <?php echo date('d F Y', strtotime($article['date'])); ?>
                    <?php if ($article['authors']): ?>
                        | Oleh: <?php echo htmlspecialchars($article['authors']); ?>
                    <?php endif; ?>
                    <?php if ($article['categories']): ?>
                        | Kategori: <?php echo htmlspecialchars($article['categories']); ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($article['picture']): ?>
                    <img src="<?php echo htmlspecialchars($article['picture']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                         class="article-image">
                <?php endif; ?>
                
                <div class="article-content">
                    <?php 
                    // Display HTML content directly (since it comes from TinyMCE)
                    // Use strip_tags with allowed tags for security, or implement proper HTML sanitization
                    $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><table><tr><td><th><thead><tbody><tfoot><hr><div><span>';
                    echo strip_tags($article['content'], $allowed_tags);
                    ?>
                </div>
            </div>
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
                <h3 class="sidebar-title">Artikel Terkait</h3>
                <?php foreach ($related_articles as $related): ?>
                    <a href="article.php?id=<?php echo $related['id']; ?>" class="related-article">
                        <?php echo htmlspecialchars($related['title']); ?>
                    </a>
                <?php endforeach; ?>
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
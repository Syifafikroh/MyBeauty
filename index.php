<?php
// index.php - Homepage (Fixed untuk mengatasi ERR_TOO_MANY_REDIRECTS)

// PENTING: Jangan ada output sebelum ini (whitespace, echo, dll)
ob_start(); // Start output buffering untuk menghindari premature output

include_once 'config.php';

// Initialize variables dengan default values
$articles = [];
$categories = [];
$search_results = null;
$database_error = false;

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        $articles = getLatestArticles($conn, 7);
        $categories = getCategories($conn);
        
        // Handle search
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $search_results = searchArticles($conn, trim($_GET['search']));
        }
    } else {
        $database_error = true;
        error_log("Database connection failed in index.php");
    }
} catch (Exception $e) {
    $database_error = true;
    error_log("Exception in index.php: " . $e->getMessage());
}

// Pastikan tidak ada redirect yang tidak perlu
// Jika ada error database, tetap tampilkan halaman dengan pesan error
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyBeauty - Blog Kecantikan dan Gaya Hidup</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- TAMBAHAN: Meta tags untuk mencegah cache issues -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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

<section class="hero">
    <div class="container">
        <h1>Welcome To MyBeauty</h1>
        <p>Blog Kecantikan dan Gaya Hidup untuk Wanita Modern</p>
    </div>
</section>

<div class="container">
    <div class="main-content">
        <main class="content">
            <?php if ($database_error): ?>
                <div class="article-card" style="background: #ffe6e6; border-left: 4px solid #ff6b6b;">
                    <h2 style="color: #d63031;">Database Connection Error</h2>
                    <p>Maaf, sedang terjadi masalah dengan koneksi database. Silakan coba lagi nanti atau hubungi administrator.</p>
                    <p><strong>Info teknis:</strong> Periksa konfigurasi database di config.php</p>
                </div>
            <?php endif; ?>

            <?php if ($search_results !== null): ?>
                <div style="padding: 2rem; border-bottom: 1px solid #eee;">
                    <h2>Hasil Pencarian untuk "<?php echo htmlspecialchars($_GET['search']); ?>"</h2>
                    <p><?php echo count($search_results); ?> artikel ditemukan</p>
                </div>
                <?php $articles = $search_results; ?>
            <?php endif; ?>

            <?php if (empty($articles) && !$database_error): ?>
                <div class="article-card">
                    <h2>Belum ada artikel</h2>
                    <p>Artikel akan segera hadir! Saat ini database mungkin masih kosong atau sedang dalam proses setup.</p>
                    
                    <!-- DEMO ARTICLE untuk testing -->
                    <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                        <h3>Demo Article</h3>
                        <p><strong>Tanggal:</strong> <?php echo date('d M Y'); ?></p>
                        <p><strong>Judul:</strong> Tips Kecantikan Natural untuk Kulit Sehat</p>
                        <p><strong>Preview:</strong> Dapatkan kulit sehat dan bersinar dengan tips kecantikan natural yang mudah diterapkan sehari-hari...</p>
                        <p><em>*Ini adalah contoh artikel. Artikel asli akan muncul setelah database terkoneksi dengan benar.</em></p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <?php if (!empty($article['picture'])): ?>
                            <img src="<?php echo htmlspecialchars($article['picture']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                 class="article-image"
                                 onerror="this.style.display='none';">
                        <?php endif; ?>
                        
                        <div class="article-meta">
                            <?php echo date('d M Y', strtotime($article['date'])); ?>
                            <?php if (!empty($article['categories'])): ?>
                                | Kategori: <?php echo htmlspecialchars($article['categories']); ?>
                            <?php endif; ?>
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
                    <input type="text" name="search" placeholder="Cari artikel..." class="search-input" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn">Cari</button>
                </form>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Kategori</h3>
                <ul class="category-list">
                    <?php if (empty($categories)): ?>
                        <li><a href="#">Skincare (Demo)</a></li>
                        <li><a href="#">Makeup (Demo)</a></li>
                        <li><a href="#">Hair Care (Demo)</a></li>
                        <li><a href="#">Lifestyle (Demo)</a></li>
                        <li style="font-size: 0.9em; color: #999; font-style: italic;">
                            *Kategori demo - akan diganti dengan data asli dari database
                        </li>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="category.php?id=<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Tentang</h3>
                <p>MyBeauty adalah blog yang menghadirkan tips kecantikan, perawatan kulit, makeup, dan gaya hidup untuk wanita modern. Temukan inspirasi kecantikan Anda bersama kami!</p>
                
                <?php if ($database_error): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 8px; font-size: 0.9em;">
                        <strong>Status:</strong> Mode Demo (Database belum terkoneksi)
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<!-- Section Tentang -->
<section id="tentang" class="about-section">
    <div class="container">
        <h2>Tentang MyBeauty</h2>
        <div class="about-content">
            <p>MyBeauty adalah platform digital yang didedikasikan untuk memberikan informasi terkini seputar dunia kecantikan dan gaya hidup. Kami hadir untuk membantu wanita Indonesia tampil lebih percaya diri dengan tips dan trik kecantikan yang mudah diterapkan.</p>
            
            <p>Tim kami terdiri dari beauty enthusiast dan profesional yang berpengalaman dalam industri kecantikan. Setiap artikel yang kami sajikan telah melalui riset mendalam untuk memastikan informasi yang akurat dan bermanfaat.</p>
            
            <p>Kami menyediakan tips perawatan wajah, makeup, dan skincare terbaru untuk kebutuhan kecantikan Anda. Selain itu, kami juga memberikan review jujur berbagai produk kecantikan dari brand lokal dan internasional, serta inspirasi gaya hidup sehat dan fashion untuk wanita modern.</p>
        </div>
    </div>
</section>

<!-- Section Kontak -->
<section id="kontak" class="contact-section">
    <div class="container">
        <h2>Hubungi Kami</h2>
        <div class="contact-content">
            <p>Ingin berbagi cerita atau punya pertanyaan seputar kecantikan? Jangan ragu untuk menghubungi kami melalui:</p>
            
            <div class="contact-links">
                <p><strong>Instagram:</strong> @mybeauty_id</p>
            </div>
            
            <p>Kami akan dengan senang hati membantu dan menjawab pertanyaan Anda!</p>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <p>&copy; 2025 MyBeauty. All rights reserved.</p>
    </div>
</footer>

<a href="admin/login.php" class="admin-btn">Admin</a>

<?php
// End output buffering and flush
ob_end_flush();
?>

</body>
</html>
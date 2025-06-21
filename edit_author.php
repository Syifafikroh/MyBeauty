<?php
// admin/edit_author.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

$message = '';
$error = '';
$author = null;

// Get author ID from URL
$author_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch author data
if ($author_id) {
    try {
        $query = "SELECT * FROM author WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $author_id);
        $stmt->execute();
        $author = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$author) {
            header('Location: manage_authors.php');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Gagal mengambil data penulis: ' . $e->getMessage();
    }
} else {
    header('Location: manage_authors.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($nickname) && !empty($email)) {
        try {
            // Check if email already exists (except current author)
            $check_query = "SELECT COUNT(*) FROM author WHERE email = :email AND id != :id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->bindParam(':id', $author_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $error = 'Email sudah digunakan oleh penulis lain!';
            } else {
                // Update query - only update password if provided
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE author SET nickname = :nickname, email = :email, password = :password WHERE id = :id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':id', $author_id);
                } else {
                    $query = "UPDATE author SET nickname = :nickname, email = :email WHERE id = :id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':id', $author_id);
                }
                
                $stmt->execute();
                $message = 'Data penulis berhasil diperbarui!';
                
                // Refresh author data
                $query = "SELECT * FROM author WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $author_id);
                $stmt->execute();
                $author = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = 'Gagal memperbarui data penulis: ' . $e->getMessage();
        }
    } else {
        $error = 'Nickname dan email harus diisi!';
    }
}

// Get article count for this author
$article_count = 0;
try {
    $count_query = "SELECT COUNT(*) FROM article_author WHERE author_id = :author_id";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bindParam(':author_id', $author_id);
    $count_stmt->execute();
    $article_count = $count_stmt->fetchColumn();
} catch (Exception $e) {
    // If there's an error getting count, just keep it as 0
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penulis - MyBeauty Admin</title>
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
            max-width: 800px;
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

        .content {
            padding: 2rem 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            margin-right: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #28a745;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #dc3545;
        }

        .help-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .author-info {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }

        .author-info h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .article-count {
            background: #667eea;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .admin-nav {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-menu {
                gap: 1rem;
            }

            .button-group {
                flex-direction: column;
                align-items: stretch;
            }

            .submit-btn {
                margin-right: 0;
                margin-bottom: 0.5rem;
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
    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Edit Penulis</h1>
            <a href="manage_authors.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($author): ?>
            <div class="author-info">
                <h3>Informasi Penulis</h3>
                <p><strong>ID:</strong> <?php echo $author['id']; ?></p>
                <p><strong>Jumlah Artikel:</strong> 
                    <span class="article-count"><?php echo $article_count; ?> artikel</span>
                </p>
                <p><strong>Terdaftar:</strong> <?php echo isset($author['created_at']) ? date('d M Y H:i', strtotime($author['created_at'])) : 'Tidak diketahui'; ?></p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="nickname">Nama/Nickname *</label>
                        <input type="text" id="nickname" name="nickname" required 
                               placeholder="Masukkan nama atau nickname penulis"
                               value="<?php echo htmlspecialchars($author['nickname']); ?>">
                        <div class="help-text">Nama yang akan ditampilkan sebagai penulis artikel</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="email@example.com"
                               value="<?php echo htmlspecialchars($author['email']); ?>">
                        <div class="help-text">Email harus unik untuk setiap penulis</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Kosongkan jika tidak ingin mengubah password">
                        <div class="help-text">Hanya isi jika ingin mengubah password. Kosongkan untuk mempertahankan password lama.</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="submit-btn">Perbarui Data Penulis</button>
                        <button type="button" class="delete-btn" 
                                onclick="if(confirm('Yakin ingin menghapus penulis ini? <?php echo $article_count > 0 ? 'Penulis ini memiliki ' . $article_count . ' artikel.' : ''; ?>')) { window.location.href='manage_authors.php?delete=<?php echo $author['id']; ?>'; }">
                            Hapus Penulis
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
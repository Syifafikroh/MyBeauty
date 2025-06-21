<?php
// admin/edit_category.php
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
$category = null;

// Get category ID from URL
$category_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch category data
if ($category_id) {
    try {
        $query = "SELECT * FROM category WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $category_id);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            header('Location: manage_categories.php');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Gagal mengambil data kategori: ' . $e->getMessage();
    }
} else {
    header('Location: manage_categories.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    if (!empty($name)) {
        try {
            // Check if name already exists (except current category)
            $check_query = "SELECT COUNT(*) FROM category WHERE name = :name AND id != :id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':name', $name);
            $check_stmt->bindParam(':id', $category_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $error = 'Nama kategori sudah digunakan!';
            } else {
                $query = "UPDATE category SET name = :name, description = :description WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':id', $category_id);
                $stmt->execute();
                
                $message = 'Kategori berhasil diperbarui!';
                
                // Refresh category data
                $query = "SELECT * FROM category WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $category_id);
                $stmt->execute();
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = 'Gagal memperbarui kategori: ' . $e->getMessage();
        }
    } else {
        $error = 'Nama kategori harus diisi!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - MyBeauty Admin</title>
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
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

        .category-info {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }

        .category-info h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            align-items: center;
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
            <h1 class="page-title">Edit Kategori</h1>
            <a href="manage_categories.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($category): ?>
            <div class="category-info">
                <h3>Informasi Kategori</h3>
                <p><strong>ID:</strong> <?php echo $category['id']; ?></p>
                <p><strong>Dibuat:</strong> <?php echo isset($category['created_at']) ? date('d M Y H:i', strtotime($category['created_at'])) : 'Tidak diketahui'; ?></p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Nama Kategori *</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Masukkan nama kategori"
                               value="<?php echo htmlspecialchars($category['name']); ?>">
                        <div class="help-text">Contoh: Perawatan Kulit, Makeup, dll.</div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" 
                                  placeholder="Deskripsi kategori (opsional)"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        <div class="help-text">Jelaskan tentang kategori ini</div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="submit-btn">Perbarui Kategori</button>
                        <button type="button" class="delete-btn" 
                                onclick="if(confirm('Yakin ingin menghapus kategori ini? Semua artikel dalam kategori ini akan kehilangan kategorinya.')) { window.location.href='manage_categories.php?delete=<?php echo $category['id']; ?>'; }">
                            Hapus Kategori
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
// admin/edit_article.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$database = new Database();
$conn = $database->getConnection();

$article_id = isset($_GET['id']) ? $_GET['id'] : 0;
$message = '';
$error = '';

// Get article data
$query = "SELECT * FROM article WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $article_id);
$stmt->execute();
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: manage_articles.php');
    exit;
}

// Get article categories
$cat_query = "SELECT category_id FROM article_category WHERE article_id = :id";
$cat_stmt = $conn->prepare($cat_query);
$cat_stmt->bindParam(':id', $article_id);
$cat_stmt->execute();
$article_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get article authors
$auth_query = "SELECT author_id FROM article_author WHERE article_id = :id";
$auth_stmt = $conn->prepare($auth_query);
$auth_stmt->bindParam(':id', $article_id);
$auth_stmt->execute();
$article_authors = $auth_stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $picture = $_POST['picture'];
    $categories = $_POST['categories'] ?? [];
    $authors = $_POST['authors'] ?? [];
    $date = $_POST['date'];

    if (!empty($title) && !empty($content)) {
        try {
            // Update article
            $query = "UPDATE article SET title = :title, content = :content, picture = :picture, date = :date WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':picture', $picture);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':id', $article_id);
            $stmt->execute();
            
            // Delete existing categories and authors
            $conn->prepare("DELETE FROM article_category WHERE article_id = :id")->execute([':id' => $article_id]);
            $conn->prepare("DELETE FROM article_author WHERE article_id = :id")->execute([':id' => $article_id]);
            
            // Insert new categories
            if (!empty($categories)) {
                $cat_query = "INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)";
                $cat_stmt = $conn->prepare($cat_query);
                foreach ($categories as $category_id) {
                    $cat_stmt->bindParam(':article_id', $article_id);
                    $cat_stmt->bindParam(':category_id', $category_id);
                    $cat_stmt->execute();
                }
            }
            
            // Insert new authors
            if (!empty($authors)) {
                $auth_query = "INSERT INTO article_author (article_id, author_id) VALUES (:article_id, :author_id)";
                $auth_stmt = $conn->prepare($auth_query);
                foreach ($authors as $author_id) {
                    $auth_stmt->bindParam(':article_id', $article_id);
                    $auth_stmt->bindParam(':author_id', $author_id);
                    $auth_stmt->execute();
                }
            }
            
            // Update arrays for display
            $article_categories = $categories;
            $article_authors = $authors;
            $article['title'] = $title;
            $article['content'] = $content;
            $article['picture'] = $picture;
            $article['date'] = $date;
            
            $message = 'Artikel berhasil diupdate!';
        } catch (Exception $e) {
            $error = 'Gagal mengupdate artikel: ' . $e->getMessage();
        }
    } else {
        $error = 'Judul dan konten artikel harus diisi!';
    }
}

// Get categories and authors
$categories = $conn->query("SELECT * FROM category ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$authors = $conn->query("SELECT * FROM author ORDER BY nickname")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - MyBeauty Admin</title>
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #667eea;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .submit-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #556cd6;
        }

        .message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .help-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        /* Custom editor styles */
        .editor-container {
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            transition: border-color 0.3s;
        }

        .editor-container:focus-within {
            border-color: #667eea;
        }

        .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 0.5rem;
            border-radius: 8px 8px 0 0;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .editor-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .editor-btn:hover {
            background: #e9ecef;
        }

        .editor-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .editor-content {
            min-height: 300px;
            padding: 1rem;
            outline: none;
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

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

            .editor-toolbar {
                padding: 0.3rem;
                gap: 0.3rem;
            }

            .editor-btn {
                padding: 0.4rem;
                font-size: 0.8rem;
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
            <h1 class="page-title">Edit Artikel</h1>
            <a href="manage_articles.php" class="back-btn">← Kembali</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="articleForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Judul Artikel *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($article['title']); ?>"
                               placeholder="Masukkan judul artikel">
                    </div>

                    <div class="form-group">
                        <label for="date">Tanggal Publikasi</label>
                        <input type="date" id="date" name="date" 
                               value="<?php echo $article['date'] ? date('Y-m-d', strtotime($article['date'])) : date('Y-m-d'); ?>">
                        <div class="help-text">Ubah tanggal publikasi artikel</div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="picture">URL Gambar</label>
                    <input type="url" id="picture" name="picture" 
                           value="<?php echo htmlspecialchars($article['picture']); ?>"
                           placeholder="https://example.com/gambar.jpg">
                    <div class="help-text">Masukkan URL gambar untuk artikel (opsional)</div>
                </div>

                <div class="form-group full-width">
                    <label for="content">Konten Artikel *</label>
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-action="bold"><b>B</b></button>
                            <button type="button" class="editor-btn" data-action="italic"><i>I</i></button>
                            <button type="button" class="editor-btn" data-action="underline"><u>U</u></button>
                            <button type="button" class="editor-btn" data-action="insertUnorderedList">• List</button>
                            <button type="button" class="editor-btn" data-action="insertOrderedList">1. List</button>
                            <button type="button" class="editor-btn" data-action="justifyLeft">Left</button>
                            <button type="button" class="editor-btn" data-action="justifyCenter">Center</button>
                            <button type="button" class="editor-btn" data-action="justifyRight">Right</button>
                        </div>
                        <div class="editor-content" id="editor" contenteditable="true"></div>
                    </div>
                    <textarea id="content" name="content" style="display: none;" required></textarea>
                    <div class="help-text">Edit konten artikel Anda</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <div class="checkbox-group">
                            <?php foreach ($categories as $category): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="cat_<?php echo $category['id']; ?>" 
                                           name="categories[]" value="<?php echo $category['id']; ?>"
                                           <?php echo in_array($category['id'], $article_categories) ? 'checked' : ''; ?>>
                                    <label for="cat_<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($categories)): ?>
                            <div class="help-text">Belum ada kategori. <a href="add_category.php">Tambah kategori</a></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Penulis</label>
                        <div class="checkbox-group">
                            <?php foreach ($authors as $author): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="auth_<?php echo $author['id']; ?>" 
                                           name="authors[]" value="<?php echo $author['id']; ?>"
                                           <?php echo in_array($author['id'], $article_authors) ? 'checked' : ''; ?>>
                                    <label for="auth_<?php echo $author['id']; ?>">
                                        <?php echo htmlspecialchars($author['nickname']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($authors)): ?>
                            <div class="help-text">Belum ada penulis. <a href="add_author.php">Tambah penulis</a></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Artikel</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('editor');
    const contentTextarea = document.getElementById('content');
    const editorButtons = document.querySelectorAll('.editor-btn');

    // Initialize editor with existing content
    const existingContent = <?php echo json_encode($article['content']); ?>;
    if (existingContent && existingContent.trim() !== '') {
        editor.innerHTML = existingContent;
        contentTextarea.value = existingContent;
    } else {
        editor.innerHTML = '<p>Edit konten artikel di sini...</p>';
    }

    // Handle editor focus
    editor.addEventListener('focus', function() {
        if (this.innerHTML === '<p>Edit konten artikel di sini...</p>') {
            this.innerHTML = '';
        }
    });

    // Handle editor blur
    editor.addEventListener('blur', function() {
        if (this.innerHTML.trim() === '' || this.innerHTML === '<p><br></p>') {
            this.innerHTML = '<p>Edit konten artikel di sini...</p>';
        }
    });

    // Handle toolbar buttons
    editorButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            
            // Focus editor first
            editor.focus();
            
            // Execute command
            document.execCommand(action, false, null);
            
            // Update button state
            updateButtonStates();
        });
    });

    // Update button states based on current selection
    function updateButtonStates() {
        editorButtons.forEach(button => {
            const action = button.getAttribute('data-action');
            const isActive = document.queryCommandState(action);
            button.classList.toggle('active', isActive);
        });
    }

    // Update button states on selection change
    editor.addEventListener('mouseup', updateButtonStates);
    editor.addEventListener('keyup', updateButtonStates);

    // Sync content with hidden textarea
    editor.addEventListener('input', function() {
        contentTextarea.value = this.innerHTML;
    });

    // Form submission handler
    document.getElementById('articleForm').addEventListener('submit', function(e) {
        // Update content textarea
        contentTextarea.value = editor.innerHTML;
        
        // Get content from editor
        const content = editor.innerHTML.trim();
        
        // Validate content
        if (!content || content === '<p>Edit konten artikel di sini...</p>' || content === '<p><br></p>') {
            e.preventDefault();
            alert('Konten artikel harus diisi!');
            editor.focus();
            return false;
        }
    });

    // Handle paste events to clean up formatting
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
    });
});
</script>

</body>
</html>
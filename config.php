<?php
// config.php - Database Configuration for InfinityFree Hosting
class Database {
    // InfinityFree Database Configuration
    private $host = 'sql200.infinityfree.com'; 
    private $db_name = 'if0_37969473_beautyblog'; 
    private $username = 'if0_37969473'; 
    private $password = ''; 
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error instead of echoing - PENTING: Jangan echo/print saat error
            error_log("Database connection error: " . $exception->getMessage());
            // Return null instead of false to avoid redirect issues
            return null;
        }
        return $this->conn;
    }
}

// functions.php - Helper Functions with Error Handling
function getLatestArticles($conn, $limit = 7) {
    // Jika connection null, return empty array instead of false
    if (!$conn) return [];
    
    try {
        $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories 
                  FROM article a 
                  LEFT JOIN article_category ac ON a.id = ac.article_id 
                  LEFT JOIN category c ON ac.category_id = c.id 
                  GROUP BY a.id 
                  ORDER BY a.date DESC 
                  LIMIT :limit";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getLatestArticles: " . $e->getMessage());
        return [];
    }
}

function getCategories($conn) {
    if (!$conn) return [];
    
    try {
        $query = "SELECT * FROM category ORDER BY name";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCategories: " . $e->getMessage());
        return [];
    }
}

function getArticleById($conn, $id) {
    // Return null instead of false untuk consistency
    if (!$conn) return null;
    
    try {
        $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories,
                  GROUP_CONCAT(au.nickname) as authors
                  FROM article a 
                  LEFT JOIN article_category ac ON a.id = ac.article_id 
                  LEFT JOIN category c ON ac.category_id = c.id 
                  LEFT JOIN article_author aa ON a.id = aa.article_id
                  LEFT JOIN author au ON aa.author_id = au.id
                  WHERE a.id = :id 
                  GROUP BY a.id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getArticleById: " . $e->getMessage());
        return null;
    }
}

function getArticlesByCategory($conn, $category_id) {
    if (!$conn) return [];
    
    try {
        $query = "SELECT a.*, c.name as category_name 
                  FROM article a 
                  JOIN article_category ac ON a.id = ac.article_id 
                  JOIN category c ON ac.category_id = c.id 
                  WHERE c.id = :category_id 
                  ORDER BY a.date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getArticlesByCategory: " . $e->getMessage());
        return [];
    }
}

function searchArticles($conn, $keyword) {
    if (!$conn) return [];
    
    try {
        $query = "SELECT a.*, GROUP_CONCAT(c.name) as categories 
                  FROM article a 
                  LEFT JOIN article_category ac ON a.id = ac.article_id 
                  LEFT JOIN category c ON ac.category_id = c.id 
                  WHERE a.title LIKE :keyword OR a.content LIKE :keyword 
                  GROUP BY a.id 
                  ORDER BY a.date DESC";
        $stmt = $conn->prepare($query);
        $keyword = "%$keyword%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in searchArticles: " . $e->getMessage());
        return [];
    }
}

// Function to check if database connection is working
function testDatabaseConnection() {
    $database = new Database();
    $conn = $database->getConnection();
    if ($conn) {
        return true;
    } else {
        return false;
    }
}

// TAMBAHAN: Function untuk handle error tanpa menyebabkan redirect
function handleDatabaseError() {
    // Jangan echo/print apapun di sini
    // Hanya log error saja
    error_log("Database connection failed - using fallback data");
    
    // Return dummy data untuk testing
    return [
        'status' => 'error',
        'message' => 'Database connection failed'
    ];
}
?>
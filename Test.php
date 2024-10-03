<?php

//Create and implement database connection class
class Database {
    private $host = 'localhost';
    private $dbName = 'newdb';
    private $username = 'root';
    private $password = '123456';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbName, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }
}

$dbClass = new Database();
$conn = $dbClass->connect();

//Implement image download and save functionality with directory check
$imageUrl = "https://cdn2.vectorstock.com/i/1000x1000/23/81/default-avatar-profile-icon-vector-18942381.jpg";
$saveTo = "images/default-avatar-profile.jpg";

if (!is_dir('images')) {
    mkdir('images', 0777, true);
}

if (!file_exists($saveTo)) {
    $imageData = file_get_contents($imageUrl);

    if ($imageData !== false) {
        file_put_contents($saveTo, $imageData);
        echo "Image downloaded and saved successfully!<br>";
    } else {
        echo "Failed to download the image.<br>";
    }
}


$currentMonth = date('m');

//Retrieve and display users with birthdays this month and their recent posts
$query = "
    SELECT 
        users.id AS user_id, 
        users.name AS user_name, 
        users.email AS user_email, 
        users.birth_date, 
        posts.title AS post_title, 
        posts.body AS post_body, 
        posts.created_date AS post_date 
    FROM 
        users 
    JOIN 
        posts 
    ON 
        users.id = posts.userId 
    WHERE 
        MONTH(users.birth_date) = :currentMonth 
    ORDER BY 
        posts.created_date DESC
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->execute([':currentMonth' => $currentMonth]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Users with Birthdays This Month and Their Most Recent Post</h2>";
foreach ($results as $result) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
    echo "<img src='images/default-avatar-profile.jpg' alt='Avatar' style='width: 50px; height: 50px; border-radius: 50%; float: left; margin-right: 10px;'>";
    echo "<strong>" . htmlspecialchars($result['user_name']) . " (" . htmlspecialchars($result['user_email']) . ")</strong><br>";
    echo "<em>Birthday: " . htmlspecialchars($result['birth_date']) . "</em><br>";
    echo "<em>Post Title: " . htmlspecialchars($result['post_title']) . "</em><br>";
    echo "<p>" . htmlspecialchars($result['post_body']) . "</p>";
    echo "<small>Posted on: " . htmlspecialchars($result['post_date']) . "</small>";
    echo "</div>";
}


//Create and display post count report by hour and date
$query = "
    SELECT 
        DATE(created_date) AS post_date, 
        HOUR(created_date) AS post_hour, 
        COUNT(*) AS post_count
    FROM 
        posts
    GROUP BY 
        post_date, post_hour
    ORDER BY 
        post_date, post_hour;
";

$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display results in an HTML table
echo "<h2>Posts Count by Hour</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>תאריך</th><th>שעה</th><th>כמות פוסטים לאותה שעה</th></tr>";

foreach ($results as $result) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($result['post_date']) . "</td>";
    echo "<td>" . htmlspecialchars($result['post_hour']) . "</td>";
    echo "<td>" . htmlspecialchars($result['post_count']) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>

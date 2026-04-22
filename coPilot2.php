function searchUsersByLastName(UserDB $db) {
    // Get last_name from GET parameter
    $lastName = $_GET['last_name'] ?? '';

    // Basic validation
    if ($lastName === '') {
        echo "<p>No search term provided.</p>";
        return;
    }

    // Escape the input to avoid SQL injection
    $safeLastName = addslashes($lastName);

    // Build query based on your class pattern
    $sql = "SELECT id, first_name, last_name, email 
            FROM users 
            WHERE last_name LIKE '%{$safeLastName}%'";

    $results = $db->query($sql);

    // Display results in an HTML table
    echo "<table border='1' cellpadding='6'>";
    echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th></tr>";

    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['first_name']}</td>";
        echo "<td>{$row['last_name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "</tr>";
    }

    echo "</table>";
}

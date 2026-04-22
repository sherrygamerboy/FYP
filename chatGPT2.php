<?php
function searchUsersByLastName($userDb) {
    if (!isset($_GET['last_name']) || $_GET['last_name'] === '') {
        echo "Please provide a last name.";
        return;
    }

    // Basic sanitization (better would be prepared statements, see note below)
    $lastName = trim($_GET['last_name']);
    $lastNameEscaped = addslashes($lastName);

    // Build query
    $sql = "SELECT id, first_name, last_name, email 
            FROM users 
            WHERE last_name LIKE '%$lastNameEscaped%'";

    $result = $userDb->query($sql);

    // Output results in HTML table
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th></tr>";

    if ($result) {
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "</tr>";
        }
    }

    echo "</table>";
}
?>
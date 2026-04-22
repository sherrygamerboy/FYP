function displayUserSearch(UserDB $userDb) {
    // 1. Get the last name from the GET parameter
    $lastName = $_GET['last_name'] ?? '';

    // 2. Sanitize the input
    // Since the query method takes a raw string, we must prevent SQL Injection
    // Note: If using PDO, ideally use real_escape_string or similar for the specific driver
    $safeLastName = addslashes($lastName); 

    // 3. Build and execute the query
    // Using LIKE allows for partial matches (e.g., "Smi" finds "Smith")
    $sql = "SELECT id, first_name, last_name, email FROM users WHERE last_name LIKE '%$safeLastName%'";
    $results = $userDb->query($sql);

    // 4. Render the HTML Table
    echo "<h2>User Search Results</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
            </tr>
          </thead>";
    echo "<tbody>";

    if ($results && count($results) > 0) {
        foreach ($results as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No users found matching that last name.</td></tr>";
    }

    echo "</tbody>";
    echo "</table>";
}
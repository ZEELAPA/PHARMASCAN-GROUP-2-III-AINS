<?php
    // search-suggestions.php
    include('../sqlconnect.php');
    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'suggestions' => []
    ];

    // Ensure we have a search term from the GET request
    if (isset($_GET['term'])) {
        $searchTerm = trim($_GET['term']);

        if (!empty($searchTerm) && $conn) {
            // Use a prepared statement to prevent SQL injection
            // The CONCAT() function joins names, and '%' is a wildcard
            $query = "SELECT CONCAT(p.FirstName, ' ', p.LastName) AS FullName
                      FROM tblpersonalinfo p
                      JOIN tblemployees e ON p.PersonalID = e.PersonalID
                      WHERE CONCAT(p.FirstName, ' ', p.LastName) LIKE ?
                      ORDER BY FullName ASC
                      LIMIT 10"; // Limit results for performance

            if ($stmt = $conn->prepare($query)) {
                $likeTerm = "%" . $searchTerm . "%";
                $stmt->bind_param("s", $likeTerm);
                $stmt->execute();
                $result = $stmt->get_result();

                $suggestions = [];
                while ($row = $result->fetch_assoc()) {
                    $suggestions[] = $row['FullName'];
                }

                $response['success'] = true;
                $response['suggestions'] = $suggestions;
                $stmt->close();
            }
        }
        $conn->close();
    }

    echo json_encode($response);
    exit();
?>
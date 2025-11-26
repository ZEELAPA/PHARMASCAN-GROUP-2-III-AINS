<?php
    // admin-auth-handler.php
    session_start();
    include('sqlconnect.php');

    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'message' => 'Invalid request.'
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nfcCode'], $_POST['nfcPassword'])) {
        if (!$conn || $conn->connect_error) {
            $response['message'] = 'Database connection failed.';
            echo json_encode($response);
            exit();
        }

        $nfcCode = trim($_POST['nfcCode']);
        $nfcPassword = trim($_POST['nfcPassword']);

        if (empty($nfcCode) || empty($nfcPassword)) {
            $response['message'] = 'NFC Code and Password are required.';
            echo json_encode($response);
            exit();
        }

        $query = "SELECT Role, ICPassword FROM tblaccounts WHERE ICCode = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $nfcCode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $account = $result->fetch_assoc();
                
                if ($account['Role'] !== 'Administrator') {
                    $response['message'] = 'Authorization failed: User is not an administrator.';
                }
                else if ($account['ICPassword'] !== $nfcPassword) {
                    $response['message'] = 'Authorization failed: Incorrect password.';
                }
                else {
                    $response['success'] = true;
                    $response['message'] = 'Administrator verified.';
                }
            } else {
                $response['message'] = 'Authorization failed: NFC Card not recognized.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare database query.';
        }
        $conn->close();
    }

    echo json_encode($response);
    exit();
?>
<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function add_toast(string $message, string $type = 'success'): void {
        if (!isset($_SESSION['toast_messages'])) {
            $_SESSION['toast_messages'] = [];
        }
        
        $_SESSION['toast_messages'][] = [
            'message' => $message,
            'class'   => 'status-' . $type
        ];
    }

    define('SESSION_TIMEOUT', 600);

    if (isset($_SESSION['AccountID'])) {
        if (isset($_SESSION['last_activity'])) {
            
            // Calculate the time difference
            $elapsed_time = time() - $_SESSION['last_activity'];
            
            // If the elapsed time is greater than the timeout, destroy the session
            if ($elapsed_time > SESSION_TIMEOUT) {
                // Unset all session variables
                $_SESSION = array();
                // Destroy the session
                session_destroy();
                // Redirect to the login page with a message
                header("Location: login.php?status=expired");
                exit;
            }
        }
        
        // If the session is still active, update the last activity timestamp
        $_SESSION['last_activity'] = time();
    }

    /**
     * Checks if a user is logged in. If not, redirects to the login page.
     */
    function require_login() {
        if (!isset($_SESSION['AccountID'])) {
            header('Location: login.php');
            exit();
        }
    }

    function require_admin() {
        require_login();
        
        if ($_SESSION['Role'] !== 'Administrator') {
            session_destroy();
            header('Location: login.php?error=access_denied');
            exit();
        }
    }

    function require_user() {
        require_login();

        if ($_SESSION['Role'] !== 'User') {
            session_destroy();
            header('Location: login.php?error=access_denied');
            exit();
        }
    }

?>
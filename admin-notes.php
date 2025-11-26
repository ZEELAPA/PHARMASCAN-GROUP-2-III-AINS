<?php
    header('Content-Type: application/json');

    $notesFile = 'admin_notes.json';

    // Helper function to get notes from the file
    function getNotes($file) {
        if (!file_exists($file)) {
            return [];
        }
        $json_data = file_get_contents($file);
        return json_decode($json_data, true) ?: [];
    }

    // Helper function to save notes to the file
    function saveNotes($file, $notes) {
        file_put_contents($file, json_encode($notes, JSON_PRETTY_PRINT));
    }

    // Check the request method
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        
        $notes = getNotes($notesFile);

        switch ($action) {
            case 'add':
                if (!empty($data['text'])) {
                    $newNote = [
                        'id' => uniqid(), // Unique ID for each note
                        'text' => htmlspecialchars($data['text']),
                        'completed' => false
                    ];
                    $notes[] = $newNote;
                    saveNotes($notesFile, $notes);
                    echo json_encode(['status' => 'success', 'note' => $newNote]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Note text cannot be empty.']);
                }
                break;
            case 'update':
                if (!empty($data['id']) && isset($data['text'])) {
                    $noteUpdated = false;
                    foreach ($notes as &$note) { // Use a reference (&) to modify the array
                        if ($note['id'] === $data['id']) {
                            $note['text'] = htmlspecialchars($data['text']);
                            $noteUpdated = true;
                            break;
                        }
                    }
                    if ($noteUpdated) {
                        saveNotes($notesFile, $notes);
                        echo json_encode(['status' => 'success']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Note not found.']);
                    }
                } else {
                     echo json_encode(['status' => 'error', 'message' => 'Missing note ID or text.']);
                }
                break;
            case 'delete':
                if (!empty($data['id'])) {
                    $notes = array_filter($notes, function($note) use ($data) {
                        return $note['id'] !== $data['id'];
                    });
                    // Re-index the array to prevent JSON from creating an object
                    saveNotes($notesFile, array_values($notes));
                    echo json_encode(['status' => 'success']);
                }
                break;

            case 'toggle':
                if (!empty($data['id'])) {
                    foreach ($notes as &$note) {
                        if ($note['id'] === $data['id']) {
                            $note['completed'] = (bool)$data['completed'];
                            break;
                        }
                    }
                    saveNotes($notesFile, $notes);
                    echo json_encode(['status' => 'success']);
                }
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
                break;
        }
    } else {
        // If it's a GET request, just return the notes
        echo json_encode(getNotes($notesFile));
    }
?>
<?php
header('Content-Type: application/json');

try {
    require_once 'config.php';
    $database = new Database();
    $db = $database->getConnection();

    $db->beginTransaction();

    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        $required_fields = ['who', 'what', 'where', 'when', 'why', 'how'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }

        $query = "INSERT INTO reports (who, what, `where`, `when`, why, how, additional_details) 
                  VALUES (:who, :what, :where, :when, :why, :how, :details)";

        $stmt = $db->prepare($query);
        $stmt->execute([
            ':who' => $_POST['who'],
            ':what' => $_POST['what'],
            ':where' => $_POST['where'],
            ':when' => $_POST['when'],
            ':why' => $_POST['why'],
            ':how' => $_POST['how'],
            ':details' => $_POST['details'] ?? null
        ]);

        $report_id = $db->lastInsertId();

        $upload_dir = 'uploads/' . date('Y/m/');
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!empty($_FILES['photos'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024;

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_type = $_FILES['photos']['type'][$key];
                $file_size = $_FILES['photos']['size'][$key];
                $file_name = $_FILES['photos']['name'][$key];

                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("Invalid file type: $file_name");
                }

                if ($file_size > $max_size) {
                    throw new Exception("File too large: $file_name");
                }

                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . time() . '.' . $extension;
                $file_path = $upload_dir . $unique_filename;

                if (move_uploaded_file($tmp_name, $file_path)) {
                    $query = "INSERT INTO report_photos (report_id, file_name, file_path, file_size, mime_type) 
                             VALUES (:report_id, :file_name, :file_path, :file_size, :mime_type)";

                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':report_id' => $report_id,
                        ':file_name' => $file_name,
                        ':file_path' => $file_path,
                        ':file_size' => $file_size,
                        ':mime_type' => $file_type
                    ]);
                } else {
                    throw new Exception("Failed to upload file: $file_name");
                }
            }
        }

        $query = "INSERT INTO report_audit_logs (report_id, action, new_status) 
                  VALUES (:report_id, 'create', 'pending')";

        $stmt = $db->prepare($query);
        $stmt->execute([
            ':report_id' => $report_id
        ]);

        $db->commit();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Laporan berhasil dikirim',
            'report_id' => $report_id
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

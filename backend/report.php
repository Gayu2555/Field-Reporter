<?php
if (isset($_GET['id'])) {
    try {
        require_once 'config.php';
        $database = new Database();
        $db = $database->getConnection();

        // Get report details
        $query = "SELECT r.*, 
                    GROUP_CONCAT(rp.file_path) as photo_paths
                 FROM reports r
                 LEFT JOIN report_photos rp ON r.id = rp.report_id
                 WHERE r.id = :id
                 GROUP BY r.id";

        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_GET['id']]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($report) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $report
            ]);
        } else {
            throw new Exception('Report not found');
        }
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

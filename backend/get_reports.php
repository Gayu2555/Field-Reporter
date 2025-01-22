<?php
header('Content-Type: application/json');

try {
    require_once 'config.php';
    $database = new Database();
    $db = $database->getConnection();

    // Ambil parameter dari query
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $startDate = $_GET['start'] ?? null;
    $endDate = $_GET['end'] ?? null;
    $search = $_GET['search'] ?? null;

    $offset = ($page - 1) * $limit;

    // Query dasar
    $query = "SELECT r.id, r.who, r.what, r.where, r.created_at, 
              GROUP_CONCAT(DISTINCT rp.file_path) as photo_paths
              FROM reports r
              LEFT JOIN report_photos rp ON r.id = rp.report_id";

    $countQuery = "SELECT COUNT(DISTINCT r.id) as total FROM reports r";

    $whereConditions = [];
    $params = [];

    // Tambahkan filter berdasarkan tanggal
    if ($startDate) {
        $whereConditions[] = "DATE(r.created_at) >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if ($endDate) {
        $whereConditions[] = "DATE(r.created_at) <= :end_date";
        $params[':end_date'] = $endDate;
    }

    // Tambahkan filter pencarian
    if ($search) {
        $whereConditions[] = "(r.who LIKE :search OR r.what LIKE :search OR r.where LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Gabungkan kondisi `WHERE`
    if (!empty($whereConditions)) {
        $whereClause = " WHERE " . implode(' AND ', $whereConditions);
        $query .= $whereClause;
        $countQuery .= $whereClause;
    }

    // Tambahkan `GROUP BY` dan pagination
    $query .= " GROUP BY r.id ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

    // Hitung total laporan
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ambil laporan
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format foto untuk setiap laporan
    foreach ($reports as &$report) {
        $report['photos'] = $report['photo_paths'] ? explode(',', $report['photo_paths']) : [];
        unset($report['photo_paths']);
    }

    // Kirim respons
    echo json_encode([
        'success' => true,
        'reports' => $reports,
        'total' => (int)$totalCount,
        'page' => $page,
        'limit' => $limit
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving reports: ' . $e->getMessage()
    ]);
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

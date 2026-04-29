<?php
require_once __DIR__ . '/../functions.php';
requireLogin();
header('Content-Type: application/json');

$user = getCurrentUser();
$stats = getDashboardStats($user);
$userId = (int)$user['id'];
$role = $user['role'];

$response = ['status' => 'success', 'data' => $stats];

// Recent OTPs
if (isset($_GET['recent'])) {
    $pdo = getDB();
    if (in_array($role, ['admin','manager'])) {
        $stmt = $pdo->query("
            SELECT sr.*, pl.profit_amount as profit
            FROM sms_received sr
            LEFT JOIN numbers n ON sr.number = n.number
            LEFT JOIN profit_log pl ON pl.sms_received_id = sr.id
            ORDER BY sr.received_at DESC LIMIT 10
        ");
    } else {
        $userIds = getDescendantUserIds($userId);
        if (empty($userIds)) {
            $response['recent'] = [];
            echo json_encode($response);
            exit;
        }
        $ph = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $pdo->prepare("
            SELECT sr.*, pl.profit_amount as profit
            FROM sms_received sr
            JOIN numbers n ON sr.number = n.number
            JOIN profit_log pl ON pl.sms_received_id = sr.id AND pl.user_id IN ($ph)
            WHERE n.assigned_to IN ($ph)
            ORDER BY sr.received_at DESC LIMIT 10
        ");
        $params = array_merge($userIds, $userIds);
        $stmt->execute($params);
    }
    $response['recent'] = $stmt->fetchAll();
}

echo json_encode($response);

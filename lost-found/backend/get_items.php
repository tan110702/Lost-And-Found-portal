<?php
include "db.php";

$sql = "SELECT items.id, items.title, items.description, items.type, items.image, items.location, items.user_id, items.created_at,
        COALESCE((
          SELECT status
          FROM claims
          WHERE claims.item_id = items.id
          ORDER BY FIELD(status, 'approved', 'pending', 'rejected'), created_at DESC
          LIMIT 1
        ), 'none') AS claim_status
        FROM items
        ORDER BY items.created_at DESC";

$result = $conn->query($sql);
$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>

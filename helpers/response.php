<?php
function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}
?>

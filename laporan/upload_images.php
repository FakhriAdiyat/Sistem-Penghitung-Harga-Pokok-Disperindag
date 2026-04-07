<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$response = ['success' => false, 'paths' => [], 'message' => ''];

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $paths = [];
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            
            if (in_array($mime, $allowed_types)) {
                $filename = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $paths[] = 'laporan/uploads/' . $filename;
                    file_put_contents(__DIR__.'/debug_upload.txt', date('Y-m-d H:i:s') . " SUCCESS: $filename mime:$mime\n", FILE_APPEND);
                } else {
                    file_put_contents(__DIR__.'/debug_upload.txt', date('Y-m-d H:i:s') . " FAIL MOVE $filename from $tmp_name mime:$mime error:" . $_FILES['images']['error'][$key] . "\n", FILE_APPEND);
                }
            } else {
                file_put_contents(__DIR__.'/debug_upload.txt', date('Y-m-d H:i:s') . " FAIL MIME $mime for file " . $_FILES['images']['name'][$key] . "\n", FILE_APPEND);
            }
        } else {
            file_put_contents(__DIR__.'/debug_upload.txt', date('Y-m-d H:i:s') . " UPLOAD ERROR code:" . $_FILES['images']['error'][$key] . "\n", FILE_APPEND);
        }
    }
    
    if (!empty($paths)) {
        $_SESSION['dokumentasi_images'] = $paths;
        $response['success'] = true;
        $response['paths'] = $paths;
    } else {
        $response['message'] = 'No valid images uploaded. Check debug_upload.txt.';
    }
} else {
    $response['message'] = 'No files received.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>


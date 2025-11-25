<?php
// Teste simples de upload
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Upload</h1>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$upload_dir = __DIR__ . '/../uploads/';
echo "<p>Upload directory: $upload_dir</p>";
echo "<p>Directory exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "</p>";
echo "<p>Directory writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h2>Upload Result:</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    $file = $_FILES['test_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $target = $upload_dir . 'test_' . time() . '_' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $target)) {
            echo "<p style='color:green;'>✅ SUCCESS! File uploaded to: $target</p>";
        } else {
            echo "<p style='color:red;'>❌ FAILED to move file</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Upload error: {$file['error']}</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h2>Test Upload</h2>
    <input type="file" name="test_file" accept="image/*">
    <button type="submit">Upload</button>
</form>

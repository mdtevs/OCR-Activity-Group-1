<?php

require 'vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

$fileRead = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $file_name = $_FILES['file']['name'];
        $tmp_file = $_FILES['file']['tmp_name'];

        if (!session_id()) {
            session_start();
        }

        $safe_name = uniqid() . '_' . time() . '_' . preg_replace('/[^a-z0-9_.]/i', '_', strtolower($file_name));
        $target_path = __DIR__ . '/uploads/' . $safe_name;

        // Ensure the uploads directory exists and is writable
        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0775, true);
        }

        if (move_uploaded_file($tmp_file, $target_path)) {
            try {
                $ocr = new TesseractOCR($target_path);
                $ocr->executable('/usr/bin/tesseract'); // Only needed if not in PATH
                $fileRead = $ocr->run();
            } catch (Exception $e) {
                $fileRead = "OCR failed: " . $e->getMessage();
            }
        } else {
            $fileRead = "❌ File failed to upload.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Reader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row mt-5">
        <div class="col-sm-8 mx-auto">
            <div class="jumbotron">
                <h1 class="display-4">Read Text from Images</h1>
                <p class="lead">
                    <?php if (!empty($fileRead)) : ?>
                        <pre><?= htmlspecialchars($fileRead) ?></pre>
                    <?php endif; ?>
                </p>
                <hr class="my-4">
            </div>
        </div>
    </div>
    <div class="row col-sm-8 mx-auto">
        <div class="card mt-5">
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="filechoose">Choose File</label>
                        <input type="file" name="file" class="form-control-file" id="filechoose" required>
                        <button class="btn btn-success mt-3" type="submit" name="submit">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
</body>
</html>

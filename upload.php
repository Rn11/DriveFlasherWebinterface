<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $allowed_extensions = ['img', 'iso', 'zip', 'usb', 'bz2', 'bzip2', 'gz', 'vhd', 'gz'];
  $file = $_FILES['file'];
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($extension, $allowed_extensions)) {
    echo "Invalid file extension. Only " . implode(", ", $allowed_extensions) . " files are allowed.";
    http_response_code(403);
    exit();
  } else if ($file['size'] > 20 * 1024 * 1024 * 1024) {
    echo "File size too large. Maximum file size is 20 GB.";
    http_response_code(403);
    exit();
  } else if ($file['error'] !== UPLOAD_ERR_OK) {
    echo "File upload failed with error code " . $file['error'];
    http_response_code(403);
    exit();
  } else {
    $new_file_path = "uploads/";
    $filename = preg_replace('/[^\w\._]+/', '', $file['name']);
    $filename = str_replace(' ', '_', $filename);
    $new_file_path .= $filename;
    // Check if the file already exists on the server.
    if (file_exists($new_file_path)) {
      echo "File already exists on the server.";
      http_response_code(403);
      exit();
    } else {
      // Check if the file is an actual disk image file.
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $file['tmp_name']);
      if (!in_array($mime_type, ['application/octet-stream', 'application/x-iso9660-image', 'application/x-compressed-tar', 'application/x-gzip', 'application/x-bzip2', 'application/x-xz', 'application/x-vhd'])) {
        echo "Invalid file type. Only disk image and archive files are allowed.";
        http_response_code(403);
        exit();
      } else {
        // Move the uploaded file to the uploads directory.
        move_uploaded_file($file['tmp_name'], $new_file_path);
        echo "File uploaded successfully!";
        http_response_code(200);
        exit();
    }
    }
  }
}
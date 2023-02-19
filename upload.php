<?php

// Make sure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Define allowed file extensions
  $allowed_extensions = ['img', 'iso', 'zip', 'usb', 'bz2', 'bzip2', 'gz', 'vhd', 'gz'];

  // Get the uploaded file and its extension
  $file = $_FILES['file'];
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  // Check if the file extension is allowed
  if (!in_array($extension, $allowed_extensions)) {
    echo "Invalid file extension. Only " . implode(", ", $allowed_extensions) . " files are allowed.";
    http_response_code(422); // Unprocessable Entity status code
    exit();
  }
  // Check if the file size is too large
  else if ($file['size'] > 20 * 1024 * 1024 * 1024) {
    echo "File size too large. Maximum file size is 20 GB.";
    http_response_code(413); // Payload Too Large status code
    exit();
  }
  // Check if there was an error during file upload
  else if ($file['error'] !== UPLOAD_ERR_OK) {
    echo "File upload failed.";
    http_response_code(500); // Internal Server Error status code
    exit();
  }
  // If everything is valid, proceed with file processing
  else {
    $new_file_path = "uploads/";

    // Remove any character that is not a letter, a digit, an underscore, a dot or a hyphen from the filename
    $filename = preg_replace('/[^\w\._]+/', '', $file['name']);

    // Replace spaces with underscores in the filename
    $filename = str_replace(' ', '_', $filename);

    // Check for malicious strings in the filename
    if (preg_match('/(%00|\\x00|%0a)/i', $filename)) {
      echo "Bad request.";
      http_response_code(400); // Bad request
      exit();
    }

    // Define dangerous file extensions
    $dangerous_extensions = ['.php', '.php4', '.php5', '.phtml', '.module', '.inc', '.hphp', '.ctp', '.php2', '.php3', '.php6', '.php7', '.phps', '.pht', '.phtm', '.phtml', '.pgif', '.shtml', '.htaccess', '.phar'];

    // Get the extension of the filename and check if it's dangerous
    $filename_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($filename_extension, $dangerous_extensions)) {
      echo "Bad request.";
      http_response_code(400); // Bad request
      exit();
    }

    $new_file_path .= $filename;

    // Check if the file already exists on the server
    if (file_exists($new_file_path)) {
      echo "File already exists on the server.";
      http_response_code(409); // Conflict status code
      exit();
    }
    // Check if the file is an actual disk image file
    else {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $file['tmp_name']);
      if (!in_array($mime_type, ['application/octet-stream', 'application/x-iso9660-image', 'application/x-compressed-tar', 'application/x-gzip', 'application/x-bzip2', 'application/x-xz', 'application/x-vhd'])) {
        echo "Invalid file type. Only disk image and archive files are allowed.";
        http_response_code(422); // Unprocessable Entity status code
        exit();
      } else {
        // Move the uploaded file to the uploads directory
        move_uploaded_file($file['tmp_name'], $new_file_path);
        echo "File uploaded successfully!";
        http_response_code(200); // OK
        exit();
      }
    }
  }
}
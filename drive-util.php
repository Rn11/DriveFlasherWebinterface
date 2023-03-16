<?php
// ensure that all expected values are present and not empty
if (
    !isset($_POST['activeTab']) || empty($_POST['activeTab']) ||
    !isset($_POST['selectedDrives']) || empty($_POST['selectedDrives'])
) {
    echo json_encode(array('success' => false, 'message' => 'Error: missing or empty parameters'));
    exit();
}

// retrieve data from POST request
$activeTab = $_POST['activeTab'];
$selectedDrives = $_POST['selectedDrives'];


// check active tab and get related data
// if tab / mode is "flash"
if ($activeTab === 'tab-flash') {
    if (!isset($_POST['fileName']) || empty($_POST['fileName'])) {
        echo json_encode(array('success' => false, 'message' => 'Error: missing or empty parameters'));
        exit();
    }
    $fileName = $_POST['fileName'];
    // check if file name for path traversal or other unwanted, dangerous content
    // get the absolute path of the file
    $filepath = realpath('/var/www/xwing.dev/fwdu-driveflasher-development/uploads/' . $fileName);

    // check if the resulting path is under the intended directory
    if (strpos($filepath, '/var/www/xwing.dev/fwdu-driveflasher-development/uploads') === false) {
        echo json_encode(array('success' => false, 'message' => 'File name contains illegal characters'));
        exit();
    }
    $command = "/usr/bin/dcfldd if=/var/www/xwing.dev/fwdu-driveflasher-development/uploads/" . $fileName . " ";
    foreach ($selectedDrives as $drive) {
        $command .= "of=" . $drive . " ";
    }
    echo ("Starting flash operation with the following command:" . $command);
}
// if tab / mode is "format"
else {
    if (!isset($_POST['selectedFileSystem']) || empty($_POST['selectedFileSystem'])) {
        echo json_encode(array('success' => false, 'message' => 'Error: missing or empty parameters'));
        exit();
    }
    $selectedFileSystem = $_POST['selectedFileSystem'];
    // TODO: process formatting with $selectedDrives and $selectedFileSystem
}

// send response back to JS
$response = array('success' => true, 'message' => 'Drive operation completed successfully');
echo json_encode($response);
?>
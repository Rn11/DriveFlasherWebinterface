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
    // TODO: process flashing with $selectedDrives and $fileName
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
$response = array('status' => true, 'message' => 'Drive operation completed successfully');
echo json_encode($response);
?>
<?php
ini_set('display_errors', true);

// ensure that all expected values are present and not empty
if (
    empty($_POST['activeTab']) || empty($_POST['selectedDrives'])
) {
    echo json_encode(array('success' => false, 'message' => 'No drive or mode selected'));
    exit();
}

// retrieve data from POST request
$activeTab = $_POST['activeTab'];
$selectedDrives = $_POST['selectedDrives'];
$fileName = null;
if (isset($_POST['fileName'])) {
    $fileName = $_POST['fileName'];
}
// sanitize data
$fileName = escapeshellarg($fileName);
$fileName = str_replace("'", "", $fileName);
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $fileName)) {
    die("Invalid file name"); // Abort if the file name contains unexpected characters
}
foreach ($selectedDrives as $drive) {

    if (!preg_match('/^[a-zA-Z0-9\/_.-]+$/', $drive)) {
        die("Invalid file name"); // abort if the file name contains unexpected characters
    }
    $drive = str_replace("'", "", escapeshellarg($drive));
}

// check active tab and get related data
// if tab / mode is "flash"
if ($activeTab === 'tab-flash') {
    if (empty($_POST['fileName'])) {
        echo json_encode(array('success' => false, 'message' => 'Error: missing or empty parameters'));
        exit();
    }
    //$fileName = $_POST['fileName'];
    // check if file name for path traversal or other unwanted, dangerous content
    // get the absolute path of the file
    $filepath = realpath('/var/www/xwing.dev/fwdu-driveflasher-development/uploads/' . $fileName);

    // check if the resulting path is under the intended directory
    if ( ! str_contains($filepath, '/var/www/xwing.dev/fwdu-driveflasher-development/uploads')) {
        //echo json_encode(array('success' => false, 'message' => 'File name contains illegal characters'));
        exit();
    }
    // build command string with parameters
    $command = "/usr/bin/dcfldd if=/var/www/xwing.dev/fwdu-driveflasher-development/uploads/" . $fileName . " ";
    foreach ($selectedDrives as $drive) {
        $command .= "of=" . $drive . "1 ";
    }
    $command .= "&& sync";
    //TODO: Bla
    // send response back to JS
    $response = array('success' => true, 'message' => 'Starting flash operation with the following command:' . $command);
    print_r($command);
    unlink("/var/www/xwing.dev/fwdu-driveflasher-development/uploads/" . $fileName);
    echo json_encode($response);
}
// if tab / mode is "format"
else {
    $command = null;
    if (empty($_POST['selectedFileSystem'])) {
        echo json_encode(array('success' => false, 'message' => 'No filesystem selected!'));
        exit();
    } else if (count($selectedDrives) != 0) {
        // save selected fs in variable
        $selectedFileSystem = $_POST['selectedFileSystem'];

        //first, delete all possible partitions and then create a new partition and make a fs on that partition
        foreach ($selectedDrives as $drive) {
            // in case of undeletable partitions (that cannot be deleted by fdisk) destroy the partition table by overwriting with zero
            $command = "dd count=32 bs=2048 if=/dev/zero of=" . $drive;
            // create new partition
            $command = match ((string)$selectedFileSystem) {
                'FAT32' => "/usr/sbin/mkfs.fat -F 32 " . $drive,
                'exFAT' => "/usr/sbin/mkfs.exfat " . $drive,
                'NTFS' => "/usr/sbin/mkfs.ntfs " . $drive,
                'ext4' => "/usr/sbin/mkfs.ext4 " . $drive,
                'btrfs' => "/usr/sbin/mkfs.btrfs -f " . $drive,
                'ReiserFS' => "/usr/bin/yes | /usr/sbin/mkfs.reiserfs " . $drive,
                'F2FS' => "/usr/sbin/mkfs.f2fs -f " . $drive,
                default => "Filesystem does not match!",
            };
        }
        echo json_encode(array('success' => true, 'message' => 'Command will be: ' . $command));
    }

    // TODO: process formatting with $selectedDrives and $selectedFileSystem
}
<?php

ini_set('display_errors', true);

// ensure that all expected values are present and not empty
if (
    empty($_POST['activeTab']) || empty($_POST['selectedDrives'])
) {
    echo json_encode(['success' => false, 'message' => 'No drive or mode selected']);
    exit();
}

// retrieve data from POST request
$activeTab = $_POST['activeTab'];
$selectedDrives = $_POST['selectedDrives'];
$fileName = null;
if (isset($_POST['fileName'])) {
    $fileName = $_POST['fileName'];
    // sanitize data
    $fileName = escapeshellarg($fileName);
    $fileName = str_replace("'", "", $fileName);
    if ( ! preg_match('/^[a-zA-Z0-9_.-]+$/', $fileName)) {
        die("Invalid file name"); // Abort if the file name contains unexpected characters
    }
}
// sanitize data
foreach ($selectedDrives as $drive) {
    if ( ! preg_match('/^[a-zA-Z0-9\/_.-]+$/', $drive)) {
        die("Invalid file name"); // abort if the file name contains unexpected characters
    }
    $drive = str_replace("'", "", escapeshellarg($drive));
}

// check active tab and get related data
// if tab / mode is "flash"
if ($activeTab === 'tab-flash') {
    if (empty($_POST['fileName'])) {
        echo json_encode(['success' => false, 'message' => 'Error: missing or empty parameters']);
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
    $command .= " && sleep 3 && sync";
    // send response back to JS
    $response = ['success' => true, 'message' => 'Starting flash operation with the following command:' . $command];
    print_r($command);
    unlink("/var/www/xwing.dev/fwdu-driveflasher-development/uploads/" . $fileName);
    echo json_encode($response);
} // if tab / mode is "format"
else {
    $command = null;
    if (empty($_POST['selectedFileSystem'])) {
        echo json_encode(['success' => false, 'message' => 'No filesystem selected!']);
        exit();
    } else {
        if (count($selectedDrives) != 0) {
            // save selected fs in variable
            $selectedFileSystem = $_POST['selectedFileSystem'];
            $comamnd = "";
            foreach ($selectedDrives as $drive) {
                //first, delete all possible partitions and then create a new partition and make a fs on that partition
                // in case of undeletable partitions (that cannot be deleted by fdisk) destroy the partition table by overwriting with zero
                $command .= "wipefs -a --force " . $drive . "1 && wipefs -a --force " . $drive . " && ";
                $command .= "dd count=32 bs=2048 if=/dev/zero of=" . $drive . " && ";
                $command .= "echo -e 'n\np\n\n\n\nw\n' | fdisk " . $drive . " && ";
                // create new partition
                $command .= match ((string)$selectedFileSystem) {
                    'FAT32' => "/usr/sbin/mkfs.fat -F 32 " . $drive . "1",
                    'exFAT' => "/usr/sbin/mkfs.exfat " . $drive . "1",
                    'NTFS' => "/usr/sbin/mkfs.ntfs " . $drive . "1",
                    'ext4' => "/usr/sbin/mkfs.ext4 " . $drive . "1",
                    'btrfs' => "/usr/sbin/mkfs.btrfs -f " . $drive . "1",
                    'ReiserFS' => "/usr/bin/yes | /usr/sbin/mkfs.reiserfs " . $drive . "1",
                    'F2FS' => "/usr/sbin/mkfs.f2fs -f " . $drive . "1",
                    default => "Filesystem does not match!",
                };
                $command .= " && ";
            }
            $command .= " sync;";
            echo json_encode(['success' => true, 'message' => 'Command will be: ' . $command]);
        }
    }
}
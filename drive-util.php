<?php
// ensure that all expected values are present and not empty
if (
    !isset($_POST['activeTab']) || empty($_POST['activeTab']) ||
    !isset($_POST['selectedDrives']) || empty($_POST['selectedDrives'])
) {
    echo json_encode(array('success' => false, 'message' => 'No drive or mode selected'));
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
    // build command string with parameters
    $command = "/usr/bin/dcfldd if=/var/www/xwing.dev/fwdu-driveflasher-development/uploads/" . $fileName . " ";
    foreach ($selectedDrives as $drive) {
        $command .= "of=" . $drive . " ";
    }
    $command .= " && sync";

    // send response back to JS
    $response = array('success' => true, 'message' => 'Starting flash operation with the following command:' . $command);
    echo json_encode($response);
}
// if tab / mode is "format"
else {
    if (!isset($_POST['selectedFileSystem']) || empty($_POST['selectedFileSystem'])) {
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
            $command = "echo -e 'd\n\nd\n\nd\n\nn\np\n\n\n\nw\n' | fdisk " . $drive;
            switch ((string) $selectedFileSystem) {
                case 'FAT32':
                    $command = "/usr/sbin/mkfs.fat -F 32 " . $drive;
                    break;
                case 'exFAT':
                    $command = "/usr/sbin/mkfs.exfat " . $drive;
                    break;
                case 'NTFS':
                    $command = "/usr/sbin/mkfs.ntfs " . $drive;
                    break;
                case 'ext4':
                    $command = "/usr/sbin/mkfs.ext4 " . $drive;
                    break;
                case 'btrfs':
                    $command = "/usr/sbin/mkfs.btrfs -f " . $drive;
                    break;
                case 'ReiserFS':
                    $command = "/usr/bin/yes | /usr/sbin/mkfs.reiserfs " . $drive;
                    break;
                case 'F2FS':
                    $command = "/usr/sbin/mkfs.f2fs -f " . $drive;
                    break;
                default:
                    // nothing;
                    $command = "Filesystem does not match!";
                    break;
            }
        }
        echo json_encode(array('success' => true, 'message' => 'Command will be: ' . $command)); 
    }

    // TODO: process formatting with $selectedDrives and $selectedFileSystem
}
?>
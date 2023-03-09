<?php
$activeTab = $_POST['tab'];

if ($activeTab === 'tab-flash') {
  $fileName = $_POST['file'];
  // handle file upload here
} else if ($activeTab === 'tab-format') {
  $fileSystem = $_POST['filesystem'];
  // handle formatting here
}

$selectedDrives = isset($_POST['drives']) ? $_POST['drives'] : '';
if ($selectedDrives !== '') {
  $drivesArray = explode(',', $selectedDrives);
  // handle flashing of selected drives here
}

// send response back to client if needed
?>

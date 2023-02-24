<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Drive flasher</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<div class="divider">
  <div class="mode-selection">
    <div class="tab">
      <button class="tablinks" id="tab-flash" onclick="openTabContent(event, 'flash')">Flash image</button>
      <button class="tablinks" id="tab-format" onclick="openTabContent(event, 'format')">Format drive</button>
    </div>
    <div id="flash" class="tabcontent">
      <h3 class="sub-headline">Flash</h3>

      <div class="file-upload-container">
        <h4 class="headline">Select a file to upload</h4>
        <div id="selected-file-name">No file selected</div>
        <form id="file-upload" method=POST enctype=multipart/form-data onsubmit="uploadFile()">
          <div class="form-group">
            <input type="file" name="file" id="file" onchange="updateSelectedFile(this)"
              accept=".iso,.img,.zip,.usb,.bz2,.bzip2,.gz,.vhd,.gz">
            <label for="file">Choose a file</label>

            <div type="submit" form="file-upload" class="progress-btn" data-progress-style="fill-back">
              <div class="btn">Upload</div>
              <div class="progress"></div>
            </div>
        </form>
      </div>

    </div>
  </div>

  <div id="format" class="tabcontent">
    <h3 class="sub-headline">Format</h3>
    <div class="file-upload-container">
      <h4 class="headline">Format device</h4>
      <span>Please select a file system</span>
      <select>
        <option>NTFS</option>
        <option>FAT32</option>
        <option>exFAT</option>
        <option>ext4</option>
        <option>btrfs</option>
        <option>ReiserFS</option>
        <option>F2FS</option>
      </select>
    </div>

  </div>
  </div>



  <div class="table-flash-container">
    <h4 class="headline">Select the drives to flash</h4>
    <table class="responsive" disabled>
      <tr>
        <th>Drive</th>
        <th>Size (GB)</th>
        <th>Flash drive?</th>
      </tr>
      <?php
      exec("lsblk -d --output NAME,SIZE", $output);
      foreach ($output as $line) {
        if (preg_match('/^(.*)\s+(\d+[G|M])$/', $line, $matches)) {
          $drive = $matches[1];
          $size = preg_replace("/[GgMm]/", "", $matches[2]);
          if (preg_match("/[Mm]/", $matches[2])) {
            $size = $size / 1024;
          }
          echo "<tr><td>/dev/$drive</td><td>$size</td><td><input type='checkbox' disabled></td></tr>\n";
        }
      }
      ?>
    </table>
    <div class="tooltip">
      <button class="button btnFlash" type="button" disabled>Flash drive!</button>
      <span class="tooltiptext">Upload an image and select a drive first!</span>
    </div>
  </div>

    </div>


  <script src="./js/libs/jquery-2.1.3.min.js"></script>
  <script src="./js/index.js"></script>
</body>

</html>
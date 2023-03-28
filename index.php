<!DOCTYPE html>
<html lang="de">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drive flasher</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="./js/libs/dark.css">
    <style>
        body.swal2-height-auto {
            height: 100vh !important;
        }

        .swal2-styled.swal2-confirm {
            background-color: #77a200;
        }

        .swal2-confirm:focus {
            box-shadow: 0 0 0;
        }
    </style>
</head>

<body>
<div class="divider">
    <div class="mode-selection">
        <div class="tab">
            <button class="tablinks" id="tab-flash" onclick="openTabContent(this, 'flash');">Flash image</button>
            <button class="tablinks" id="tab-format" onclick="openTabContent(this, 'format')">Format drive</button>
        </div>
        <div id="flash" class="tabcontent">
            <div class="file-upload-container gradient-background">
                <h3 class="sub-headline">Flash a drive</h3>
                <h4 id="upload-status-headline" class="headline">Select a file to upload</h4>
                <form id="file-upload" method=POST enctype=multipart/form-data onsubmit="uploadFile()">
                    <div class="form-group">
                        <label for="file" id="selected-file-name">No file selected</label>
                        <input type="file" name="file" id="file" onchange="updateSelectedFile(this)"
                               accept=".iso,.img,.zip,.usb,.bz2,.bzip2,.gz,.vhd,.gz">
                        <label for="file">Choose a file</label>

                        <div class="progress-btn" data-progress-style="fill-back">
                            <div class="btn">Upload</div>
                            <div class="progress"></div>
                        </div>
                </form>
            </div>

        </div>
    </div>

    <div id="format" class="tabcontent">
        <div class="file-upload-container fsSelectDiv gradient-background">
            <h4 class="headline">Format device</h4>
            <span>Please select a file system</span>
            <select class="select-fs" autocomplete="off"
                    onchange="if (this.selectedIndex) blinkDiv('table-flash-container');">
                <option hidden disabled selected value> -- select an option --</option>
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


<div class="table-flash-container gradient-background">
    <h4 class="headline" id="headline-table-selection">Select the drives to flash</h4>
    <table class="responsive" disabled>
        <tr>
            <th>Drive</th>
            <th>Size (GB)</th>
            <th>Flash drive?</th>
        </tr>
        <?php
        $chkID = 0;
        exec("lsblk -d --output NAME,SIZE", $output);
        foreach ($output as $line) {
            if (preg_match('/^(.*)\s+(\d+[G|M])$/', $line, $matches)) {
                $drive = $matches[1];
                $size = preg_replace("/[GgMm]/", "", $matches[2]);
                if (preg_match("/[Mm]/", $matches[2])) {
                    $size = $size / 1024;
                }
                echo "<tr class='tr-drive'>
                        <td>/dev/$drive</td>
                        <td>$size</td>
                        <td><li class='tg-list-item'><input class='tgl tgl-switch drive-checkbox' id='cb" . $chkID . "' type='checkbox' autocomplete='off'/>
                        <label class='tgl-btn' for='cb" . $chkID . "'></label>
                        </li></td>
                        </tr>\n";
            }
            $chkID++;
        }
        ?>
    </table>
    <div class="tooltip tooltip-hover">
        <button id="btnFlash" class="button btnFlash" type="button" disabled>Flash drive!</button>
        <span class="tooltiptext">Upload an image and select a drive first!</span>
    </div>
</div>

<script src="./js/libs/jquery-2.1.3.min.js"></script>
<script src="./js/libs/sweetalert2.js"></script>
<script src="./js/index.js"></script>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Drive flasher</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

  <div class="file-upload-container">
    <h4 class="headline">Select a file to upload</h4>
    <div id="selected-file-name">No file selected</div>
    <form id="file-upload" method=POST enctype=multipart/form-data onsubmit="uploadFile()">
      <div class="form-group">
        <input type="file" name="file" id="file" onchange="updateSelectedFile(this)"
          accept=".iso,.img,.zip,.usb,.bz2,.bzip2,.gz,.vhd,.gz">
        <div><label for="file">Choose a file</label></div>

        <div type="submit" form="file-upload" class="progress-btn" data-progress-style="fill-back">
          <div class="btn">Upload</div>
          <div class="progress"></div>
        </div>
    </form>
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
          echo "<tr><td>$drive</td><td>$size</td><td><input type='checkbox' disabled></td></tr>\n";
        }
      }
      ?>
    </table>
    <button class="button btnFlash" type="button" disabled>Flash drive!</button>
  </div>
  <script src="./js/libs/jquery-2.1.3.min.js"></script>
  <script>
    // file upload status handling
    function uploadFile() {
      event.preventDefault();
      var allowed_extensions = ['iso', 'img', 'zip', 'usb', 'bz2', 'bzip2', 'gz', 'vhd', 'gz'];
      var file = document.getElementById("file").files[0];
      if (file) {
        // filter for invalid file extension to prevent
        var extension = file.name.split('.').pop();
        if (allowed_extensions.indexOf(extension) !== -1) {
          var formData = new FormData();
          formData.append("file", file);
          var xhr = new XMLHttpRequest();
          xhr.open("POST", "upload.php", true);
          xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
              if (xhr.status === 200) {
                
                // Trigger upload button upload progress animation
                var progressBtn = $(".progress-btn");
                if (!progressBtn.hasClass("active")) {
                  progressBtn.addClass("active");
                  setTimeout(function () {
                    progressBtn.removeClass("active");
                  }, 10000);
                }

                console.log("Starting file upload!");
                // for enabling checkboxes in table
                // TODO: filter for system drives (mmcblk), leave them disabled
                document.querySelector(".table-flash-container table").removeAttribute("disabled");
                const checkboxes = document.querySelectorAll("input[type='checkbox']");
                checkboxes.forEach(checkbox => checkbox.removeAttribute("disabled"));
                document.querySelector(".btnFlash").removeAttribute("disabled");
              } else if (xhr.status === 400) {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "Bad request";
              }
              else if (xhr.status === 409) {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "File already exists on the server";
              }
              else if (xhr.status === 413) {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "File size too large. Maximum file size is 20 GB";
              }
              else if (xhr.status === 422) {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "Invalid file type<br> Only "
                  + allowed_extensions.join(", ") + " files are allowed";
              }
              else if (xhr.status === 500) {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "File upload failed";
              }
              else {
                console.log("File upload failed, status code: " + xhr.status);
                document.getElementById("selected-file-name").innerHTML = "General error";
              }
            }
          };
          xhr.send(formData);
        } else {
          document.getElementById("selected-file-name").innerHTML = "Invalid file type.<br>Only "
            + allowed_extensions.join(", ") + " files are allowed.";
        }
      }
    }
    // function for updating the selected file label
    function updateSelectedFile(input) {
      const selectedFile = input.files[0];
      const selectedFileName = selectedFile ? selectedFile.name : "";
      const selectedFileNameContainer = document.getElementById("selected-file-name");
      selectedFileNameContainer.textContent = selectedFileName;
    }
  </script>

  <script>
    // for colouring selected table rows green / resetting color back to normal via RGBA
    const checkboxes = document.querySelectorAll("input[type='checkbox']");
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener("click", function () {
        const parentRow = this.parentNode.parentNode;
        const backgroundColor = window.getComputedStyle(parentRow).getPropertyValue("background-color");
        console.log("current backgroundColor: " + backgroundColor);
        if (backgroundColor) {
          const colorValues = backgroundColor.match(/\d+(\.\d+)?/g);
          let newGreenValue = parseFloat(colorValues[1]);
          if (this.checked) {
            newGreenValue += 20;
          } else {
            newGreenValue -= 20;
          }
          console.log("newGreenValue: " + newGreenValue);
          parentRow.style.backgroundColor = `rgba(${colorValues[0]}, ${newGreenValue}, ${colorValues[2]}, ${colorValues[3]})`;
          console.log(`New color: rgba(${colorValues[0]}, ${newGreenValue}, ${colorValues[2]}, ${colorValues[3]})`);
        }
      });
    });
  </script>

  <script>
    $(document).ready(function () {
      $(".progress-btn").on("click", function () {
        var progressBtn = $(this);
        /*
                if (!progressBtn.hasClass("active")) {
                  progressBtn.addClass("active");
                  setTimeout(function () {
                    progressBtn.removeClass("active");
                  }, 10000);
                }*/
      })
    });

    $(document).ready(function () {
      $('#selected-file-name').on('drag dragstart dragend dragover dragenter dragleave drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
      })
        .on('dragover dragenter', function () {
          $(this).addClass('is-dragover');
        })
        .on('dragleave dragend drop', function () {
          $(this).removeClass('is-dragover');
        })
        .on('drop', function (event) {

          // No idea if this is the right way to do things
          $('input[type=file]').prop('files', event.originalEvent.dataTransfer.files);
          $('input[type=file]').trigger('change');
        });
    });

    // Submit button 
    $(".progress-btn").click(function () {
      $(".progress-btn").submit();
      event.preventDefault();
    });
  </script>
</body>

</html>
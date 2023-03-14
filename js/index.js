// file upload status handling
function uploadFile() {
  event.preventDefault();
  var allowed_extensions = ['iso', 'img', 'zip', 'usb', 'bz2', 'bzip2', 'gz', 'vhd', 'gz'];
  var file = document.getElementById("file").files[0];
  if (file) {
    // filter for invalid file extension to prevent file upload
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

            // for enabling the drive selection table
            // TODO: filter for system drives (mmcblk), leave them disabled
            enableTableFlashDiv();

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

// function for enabling the lower container containing the drive table and the start button (after successful file upload or after selecting a fs)
function enableTableFlashDiv() {
  document.querySelector(".table-flash-container table").removeAttribute("disabled");
  const checkboxes = document.querySelectorAll("input[type='checkbox']");
  checkboxes.forEach(checkbox => checkbox.removeAttribute("disabled"));
  document.querySelector(".btnFlash").removeAttribute("disabled");
}

// function for updating the selected file label
function updateSelectedFile(input) {
  const selectedFile = input.files[0];
  const selectedFileName = selectedFile ? selectedFile.name : "";
  const selectedFileNameContainer = document.getElementById("selected-file-name");
  selectedFileNameContainer.textContent = selectedFileName;
}

// Tab section
//make tabflash enabled by default
document.getElementById("flash").style.display = "flex";
document.getElementById("tab-flash").className += " active";
function openTabContent(evt, modeName) {
  // change flash button label and headline to format, if mode is format
  // else change labels to flash
  if (modeName == "format") {
    $("#btnFlash").html("Format drive!");
    $("#headline-table-selection").html("Select the drives to format");
  } else if (modeName == "flash") {
    $("#btnFlash").html("Flash drive!");
    $("#headline-table-selection").html("Select the drives to flash");
  }

  // for making a div blink
  $(".select-fs").change(function () {
    // set animation for element
    $(".blinkDiv").css("animation-play-state", "running");
    // remove and re-add element so animation gets reset
    var element = document.getElementsByClassName("table-flash-container")[0];
    var newElement = element.cloneNode(true);
    element.parentNode.replaceChild(newElement, element);

    // enable table flash if not yet enabled
    enableTableFlashDiv();

    // we need to add this event listener again, because when resetting the animation we remove the element, and thus the event listener for the flash button
    // this way we'll make sure the flash / format drive button always has an event listener
    $("#btnFlash").on('click', function () {
      sendData();
    });
  });

  // for switching tabs (modes)
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(modeName).style.display = "flex";
  evt.currentTarget.className += " active";
}

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

// for making a div blink
function blinkDiv(divName) {
  var element = document.getElementsByClassName(divName)[0];

  //$('#' + divName).addClass('blinkDiv');
  if (element.classList.length > 1) {
    console.log("Contains!");
    element.className = divName;
    //element.classList.remove("blinkDiv");
  }
  element.className += ' blinkDiv';
}

// send data to drive util
$(document).ready(function () {
  $("#btnFlash").on('click', function () {
    sendData();
  });
});

// this function will send the user input to drive-util.php to start flashing / formatting
function sendData() {
  // determine which tab / mode is selected
  var activeTab = $('.tablinks.active').attr('id');
  // get the selected drives from the table
  var selectedDrives = [];
  $('.drive-checkbox:checked').each(function () {
    selectedDrives.push($(this).closest('tr').find('td:first-child').text().trim());
  });

  var data = {
    'activeTab': activeTab,
    'selectedDrives': selectedDrives
  };

  // if the mode is flash, get the file name of the uploaded file
  if (activeTab === 'tab-flash') {
    data['fileName'] = $('#selected-file-name').text();
  }
  // if mode is format, get the selected filesystem 
  else {
    data['selectedFileSystem'] = $('.select-fs').val();
  }

  $.ajax({
    type: 'POST',
    url: 'drive-util.php',
    data: data,
    dataType: 'json',
    encode: true,
    success: function (response) {

      // check if operation was successful and handle success response here
      if (response.success) { // if operation was successful
        console.log("Response: " + response.success);
        Swal.fire({
          title: 'Success!',
          text: JSON.stringify(response.message).replace(/"/g, ''),
          icon: 'success',
          confirmButtonText: 'Yay'
        })

      } else { // operation did not succeed
        // fire sweetalert2 alert
        Swal.fire({
          title: 'Error!',
          text: JSON.stringify(response.message).replace(/"/g, ''),
          icon: 'error',
          confirmButtonText: 'Ok'
        })
      }
    },
    // the following will be needed if the XHR request itself threw an error, NOT for error handling from drive-util.php response
    error: function (xhr, status, error) {
      // TODO: add further error handling here
      console.log("Error: " + xhr.responseText, status, error);
      // fire sweetalert2 alert
      Swal.fire({
        title: 'Error!',
        text: xhr.responseText.replace(/"/g, ''),
        icon: 'error',
        confirmButtonText: 'Ok'
      })
    }
  });
}

// progress button
$(document).ready(function () {
  $(".progress-btn").on("click", function () {
    var progressBtn = $(this);
  })
});

// drag and drop handling
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
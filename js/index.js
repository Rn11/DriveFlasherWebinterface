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


// Tab section
//make tabflash enabled by default
document.getElementById("flash").style.display = "flex";
document.getElementById("tab-flash").className  += " active";
function openTabContent(evt, modeName) {



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


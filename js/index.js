// this function will display a sweetalert warning and is used before the user can start flashing / formatting
function displayConfirmationDialogue() {
    return Swal.fire({
        title: 'Warning',
        icon: 'warning',
        text: 'Are you sure you want to proceed? All data on the selected drives will be erased',
        showDenyButton: true,
        showCancelButton: false,
        denyButtonText: 'No',
        confirmButtonText: 'Yes',
        customClass: {
            actions: 'warning-dialogue-actions',
            cancelButton: 'order-1 right-gap',
            confirmButton: 'order-2',
            denyButton: 'order-3'
        }
    });
}

// this variable will store the sanizited filename of the upload. It's needed to pass the value to drive-util.php
let uploadFilename;

// file upload status handling
function uploadFile() {
    event.preventDefault();
    const allowed_extensions = ['iso', 'img', 'zip', 'usb', 'bz2', 'bzip2', 'gz', 'vhd', 'gz'];
    let file = document.getElementById("file").files[0];
    if (file) {
        // filter for invalid file extension to prevent file upload
        let extension = file.name.split('.').pop();
        if (allowed_extensions.indexOf(extension) !== -1) {
            let formData = new FormData();
            formData.append("file", file);
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "upload.php", true);

            $(".progress").css("opacity", 1);

            // calculate progress of upload and update UI
            xhr.upload.addEventListener("progress", ({loaded, total}) => {
                let fileLoaded = Math.floor((loaded / total) * 100);
                let fileTotal = Math.floor(total / 1000);
                let fileSize;
                fileTotal < 1024
                    ? (fileSize = fileTotal + " KB")
                    : (fileSize = (loaded / (1024 * 1024)).toFixed(2) + " MB");
                $("#upload-status-headline").text(`File upload progress: ${fileLoaded}%`);
                // update width of underlying progress-bar (upload button)
                $(".progress").css("width", fileLoaded + "%");
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        uploadFilename = xhr.responseText;
                        console.log("Successful file upload!");
                        // change file upload status description
                        $("#upload-status-headline").text("File successfully uploaded!");
                        // for enabling the drive selection table
                        // TODO: filter for system drives (mmcblk), leave them disabled
                        enableTableFlashDiv();
                        // remove tooltip
                        //$(".tooltiptext").remove();
                        $(".tooltip").removeClass('tooltip-hover');

                    } else if (xhr.status === 400) {
                        console.log("File upload failed, status code: " + xhr.status);
                        document.getElementById("selected-file-name").innerHTML = "Bad request";
                    } else if (xhr.status === 409) {
                        console.log("File upload failed, status code: " + xhr.status);
                        document.getElementById("selected-file-name").innerHTML = "File already exists on the server";
                    } else if (xhr.status === 413) {
                        console.log("File upload failed, status code: " + xhr.status);
                        document.getElementById("selected-file-name").innerHTML = "File size too large. Maximum file size is 20 GB";
                    } else if (xhr.status === 422) {
                        console.log("File upload failed, status code: " + xhr.status + " Message: " + xhr.response + " " + xhr.responseText);
                        document.getElementById("selected-file-name").innerHTML = "Invalid file type<br> Only "
                            + allowed_extensions.join(", ") + " files are allowed";
                    } else if (xhr.status === 500) {
                        console.log("File upload failed, status code: " + xhr.status);
                        document.getElementById("selected-file-name").innerHTML = "File upload failed";
                    } else {
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


// for making a div blink
$(".select-fs").change(function () {

    // set animation for element
    $(".blinkDiv").css("animation-play-state", "running");
    // remove and re-add element so animation gets reset
    const element = document.getElementsByClassName("table-flash-container")[0];
    const newElement = element.cloneNode(true);
    element.parentNode.replaceChild(newElement, element);

    // enable table flash if not yet enabled
    enableTableFlashDiv();
    colorTableRows();
    //$(".tooltiptext").remove();
    $(".tooltip").removeClass('tooltip-hover');
    // we need to add this event listener again, because when resetting the animation we remove the element, and thus the event listener for the flash button
    // this way we'll make sure the flash / format drive button always has an event listener
    $("#btnFlash").on('click', function () {
        displayConfirmationDialogue().then(function (result) {
            if (result.value) {
                sendData();
            }
        });
    });
});


// Tab section
//make tabflash enabled by default
document.getElementById("flash").style.display = "flex";
document.getElementById("tab-flash").className += " active";

function openTabContent(evt, modeName) {
    // change flash button label and headline to format, if mode is format
    // else change labels to flash
    if (modeName === "format") {
        $("#btnFlash").html("Format drive!");
        $("#headline-table-selection").html("Select the drives to format");
        $(".tooltiptext").text("Select a filesystem and a drive first!");
    } else if (modeName === "flash") {
        $("#btnFlash").html("Flash drive!");
        $("#headline-table-selection").html("Select the drives to flash");
        $(".tooltiptext").text("Upload an image and select a drive first!");
    }

    // for switching tabs (modes)
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(modeName).style.display = "flex";
    event.currentTarget.className += " active";
}


// for colouring selected table rows green / resetting color back to normal via RGBA
function colorTableRows() {
    const checkboxes = document.querySelectorAll("input[type='checkbox']");
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            const parentRow = this.parentNode.parentNode.parentNode;
            console.log(parentRow)
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
}

// for making a div blink
function blinkDiv(divName) {
    const element = document.getElementsByClassName(divName)[0];

    //$('#' + divName).addClass('blinkDiv');
    if (element.classList.length > 1) {
        console.log("Contains!");
        element.className = divName;
        //element.classList.remove("blinkDiv");
    }
    element.className += ' blinkDiv';
}

// action button click listener that will display a warning and then send the data to drive-util
$(document).ready(function () {
    $("#btnFlash").on('click', function () {
        displayConfirmationDialogue().then(function (result) {
            if (result.value) {
                sendData();
            }
        });
    });
});

// this function will send the user input to drive-util.php to start flashing / formatting
function sendData() {
    // determine which tab / mode is selected
    let activeTab = $('.tablinks.active').attr('id');
    // get the selected drives from the table
    let selectedDrives = [];
    $('.drive-checkbox:checked').each(function () {
        selectedDrives.push($(this).closest('tr').find('td:first-child').text().trim());
    });

    // preliminary check if values are empty
    if (selectedDrives.length === 0) {
        Swal.fire({
            title: 'Error',
            text: "Please select at least one drive!",
            icon: 'error',
            confirmButtonText: 'I see...'
        })
    } else {
        let data = {
            'activeTab': activeTab,
            'selectedDrives': selectedDrives
        };

        // if the mode is flash, get the file name of the uploaded file
        if (activeTab === 'tab-flash') {
            //data['fileName'] = $('#selected-file-name').text();
            data['fileName'] = uploadFilename;
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
                console.log(data);
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
}

// progress button
$(document).ready(function () {
    $(".progress-btn").on("click", function () {
        let progressBtn = $(this);
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
$(".progress-btn").on('click', function (ev) {
    ev.preventDefault();
    $(this).submit();
})
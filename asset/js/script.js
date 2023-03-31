document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="row[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

document.getElementById('deleteFile').addEventListener('click', deleteFiles);

function deleteFiles() {
    const selectedFiles = getSelectedFiles();
    if (selectedFiles.length === 0) {
        alert('Please select at least one file to delete.');
    } else {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    getFiles();
                } else {
                    alert('An error occurred while deleting the selected files.');
                }
            }
        };
        xhr.open('DELETE', 'controller/file-manager.php');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({ files: selectedFiles }));
    }
}

document.getElementById('upload-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('file', document.getElementById('file-input').files[0]);
    formData.append('path', document.getElementById('directory').innerHTML);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            const response = JSON.parse(xhr.responseText);
            if (xhr.status === 200) {
                console.log(response.message);
                getFiles();
                document.getElementById('file-input').value = '';
            } else if (xhr.status === 409) {
                alert('A file with the same name already exists.');
            } else {
                alert('An error occurred while uploading the file.');
            }
        }
    };
    xhr.open('POST', 'controller/file-manager.php');
    xhr.send(formData);
});


function getFiles() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                document.getElementById('file-list').innerHTML = xhr.responseText;
                document.getElementById('directory').innerHTML = "";
            } else {
                alert('An error occurred while retrieving the file list.');
            }
        }
    };
    xhr.open('GET', 'controller/file-manager.php?dir=home');
    xhr.send();
}

document.getElementById('createFolder').addEventListener('click', createFolder);

function createFolder() {
    const folderName = document.getElementById('foldername').value;
    const path = document.getElementById('directory').innerHTML;
    if (folderName.length > 1) {
        const formData = new FormData();
        formData.append('folder', folderName);
        formData.append('path', path);
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    getFiles();
                } else {
                    alert('An error occurred while creating the folder.');
                }
            }
        };
        xhr.open('POST', 'controller/file-manager.php');
        xhr.send(formData);
    } else {
        alert('Please enter the folder name.');
    }
}

let list = document.querySelector("#file-list");

let renameBtn = document.getElementById('rename');
renameBtn.addEventListener('click', handleRenameFile);

function handleRenameFile() {
    const fileToRename = getSelectedFiles()[0].split('.');
    const nameField = document.getElementById('renametext');
    const extensionField = document.getElementById('extension');
    if (fileToRename.length === 1) {
        nameField.value = fileToRename[0];
        extensionField.innerHTML = 'dir';
    } else {
        nameField.value = fileToRename[0];
        extensionField.innerHTML = '.' + fileToRename[1];
    }
}

document.getElementById('renameFile').addEventListener('click', renameFile);

function renameFile() {
    let text = document.getElementById('renametext').value;
    let extension = document.getElementById('extension').innerHTML;
    if (text.length > 1 && text.length < 250) {
        const selectedFiles = getSelectedFiles();
        if (selectedFiles.length === 0 || selectedFiles.length > 1) {
            alert('Please select one file to rename.');
        } else {
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log(xhr.responseText);
                        getFiles();
                    } else {
                        alert('An error occurred while renaming the selected files.');
                    }
                }
            };
            let renameTo = extension === 'dir' ? text : text + extension;
            let data = {
                "file": selectedFiles[0],
                "rename": renameTo,
            };
            xhr.open('PUT', 'controller/file-manager.php');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(data));
        }
    } else {
        alert('Please enter the file name.');
    }
}

list.addEventListener('click', handleDirectoryClick);

function handleDirectoryClick(event) {
    if (event.target.tagName === 'A' && event.target.getAttribute("class") === 'directory-link') {
        event.preventDefault();
        fetchFiles(event.target.getAttribute("href"));
    }
}

function fetchFiles(dirPath) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById('file-list').innerHTML = this.responseText;
            document.getElementById('directory').innerHTML = dirPath.replace(/^home/, "");
        }
    };
    xhr.open("GET", "controller/file-manager.php?dir=" + dirPath, true);
    xhr.send();
}

function getSelectedFiles() {
    const checkboxes = document.querySelectorAll('input[name="row[]"]');
    return Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.closest('tr').querySelector('a').getAttribute('href'));
}


<?php

handleRequest();

function handleRequest()
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    switch ($requestMethod) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        case 'DELETE':
            handleDeleteRequest();
            break;
        case 'PUT':
            handlePutRequest();
            break;
        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'Invalid request method.']);
            break;
    }
}

function handleGetRequest()
{
    if (isset($_GET['dir'])) {
        $dirPath = $_GET['dir'];
        if ($dirPath == 'undefined' || $dirPath == 'home') {
            $files = scandir('../home');
            createRow($files, 'home/');
        } else {
            $files = scandir('../' . $dirPath);
            createRow($files, $dirPath . '/');
        }
    } else {
        echo 'No directory specified';
    }
}

function handlePostRequest()
{
    if (isset($_FILES['file'])) {
        $path = $_POST['path'];
        if ($path == '') {
            uploadFile($_FILES['file']);
        } else {
            uploadFile($_FILES['file'], '../home/' . $_POST['path'] . '/');
        }
    } else if (isset($_POST['folder'])) {
        $path = $_POST['path'];
        if ($path == '') {
            createFolder($_POST['folder']);
        } else {
            createFolder($_POST['folder'], '../home/' . $_POST['path'] . '/');
        }
    }
}

function handleDeleteRequest()
{
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody, true);

    if (!isset($data['files'])) {
        http_response_code(400);
        echo json_encode(['message' => 'No files specified.']);
        return;
    }

    $files_to_delete = $data['files'];
    deleteFiles($files_to_delete);
}

function handlePutRequest()
{
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody, true);
    $file_to_rename = $data['file'];
    $new_name = $data['rename'];
    renameFile($file_to_rename, $new_name);
}


function renameFile($file_to_rename, $new_name){
    $file_path = '../' . $file_to_rename;
    $directory = dirname($file_path);
    
    $new_file_path =$directory.'/' .$new_name;
    echo $new_file_path;
    if(file_exists($file_path)) {
        rename($file_path, $new_file_path);
    }

}


function createFolder($folder_name, $parent_folder = '../home/'){
    $new_folder_name = $folder_name;

    $new_folder_path = $parent_folder . $new_folder_name;
    if (!file_exists($new_folder_path)) {
        mkdir($new_folder_path, 0777, true);
    }
}


function deleteFiles($files_to_delete){

    foreach($files_to_delete as $file) {
        $file_path = '../' . $file;
        if(file_exists($file_path)) {
            echo $file_path;
            if (is_file($file_path)) {
                
                unlink($file_path);
            } else if (is_dir($file_path)) {
                removeDir($file_path);
            }
        }
    }
}


function removeDir($dirPath) {
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    removeDir($dirPath . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        reset($objects);
        rmdir($dirPath);
    }
}


function uploadFile($file, $dirPath = '../home/'){
    $target_file = $dirPath . basename($file['name']);

    if (file_exists($target_file)) {
        http_response_code(409); // Conflict
        echo json_encode(['message' => 'A file with the same name already exists.']);
    } else {
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            http_response_code(200); // OK
            echo json_encode(['message' => 'The file has been uploaded.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => 'An error occurred while uploading the file.']);
        }
    }
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function createRow($files, $dirPath){
    $files = array_diff($files, array('.', '..')); // remove '.' and '..' entries
    foreach ($files as $file) {
        $path = $dirPath.$file;
        echo '<tr>';
        echo '<td><input type="checkbox" name="row[]" value="' .$file. '"></td>';
        if(is_dir('../'.$path)){
          echo '<td><a class="directory-link" href="' .$path. '">' . $file . '</a></td>';
        }else{
          echo '<td><a href="' .$path. '">' . $file . '</a></td>';
        }
        echo '<td>' . date('F d, Y', filemtime('../'.$path)) . '</td>';
        if(is_dir('../'.$path)){
            echo '<td>-</td>';
          }else{
            echo '<td>' . formatSizeUnits(filesize('../'.$path)) . '</td>';
          }
        echo '</tr>';
    }
}

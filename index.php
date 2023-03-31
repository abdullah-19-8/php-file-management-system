<!DOCTYPE html>
<?php
$home_dir = 'home/';
$dir_contents = array_diff(scandir($home_dir), array('..', '.'));
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="asset/css/bootstrap.min.css" rel="stylesheet">
    <script src="asset/js/bootstrap.bundle.min.js" defer></script>
    <script src="asset/js/script.js" defer></script>
    <title>MyDrive</title>
    <style>
        #file-input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .divider-line {
            width: 100%;
            background-color: #dee2e6;
            height: 1px;
            margin-left: 8px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
<header>
    
    <nav class="navbar bg-secondary navbar-dark">
        <div class="container-fluid ">
            <a class="navbar-brand">MyDrive</a>

            <ul class="nav justify-content-end column-gap-2">
                <li class="nav-item">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#fileDeleteModal">Delete</button>
                </li> 
                <li class="nav-item">
                <button type="button" id="rename" class="btn btn-light">Rename</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#folderCreateModal">New Folder</button>
                </li>
            </ul>

        </div>
    </nav>

</header>

<main>
<div class="d-flex align-items-center p-2 bg-light">
        <?php 
        echo "<div class='mx-2'><a href='#' onclick='getFiles()'>Home </a></div>";
        echo '<div id="directory"></div>'
        ?>
    </div>
    
    <div class="divider-line"></div>
    <div class="m-3">
        <form id="upload-form" enctype="multipart/form-data">
            <div class="mb-3 d-flex">
                <input id="file-input" type="file" name="file" class="form-control">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>

        <table class="table" id="table">
            <thead>
                <tr>
                    <th scope="col"><input type="checkbox" id="select-all"></th>
                    <th scope="col">Name</th>
                    <th scope="col">Last Modified</th>
                    <th scope="col">Size</th>

                </tr>
            </thead>

            <tbody class="table-group-divider" id="file-list">
            
            <?php
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
                foreach ($dir_contents as $item) {
                  $item_path = 'home/'.$item;
                  echo '<tr>';
                  echo '<td><input type="checkbox" name="row[]" value="'.$item.'"></td>';
                  if(is_dir($item_path)){
                      echo '<td><a class="directory-link" href="home/'.$item.'">' . $item . '</a></td>';
                  }else{
                      echo '<td><a href="home/'.$item.'">' . $item . '</a></td>';
                  }
                  echo '<td>' . date('F d, Y', filemtime($item_path)) . '</td>';
                  if(is_dir($item_path)){
                      echo '<td>-</td>';
                  }else{
                      echo '<td>' . formatSizeUnits(filesize($item_path)) . '</td>';
                  }
                  echo '</tr>';
              }
              

            ?>

        </tbody>
    </table>
</div>
<div class="modal fade" id="fileDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Deleting Files</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure about deleting the selected files?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" id="deleteFile" class="btn btn-danger" data-bs-dismiss="modal">Delete</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="fileRenameModal" tabindex="-1" aria-labelledby="RenameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="RenameModalLabel">Rename File</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex">
        <input id="renametext" type="text" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm">
        <span class="border bg-light"><div id="extension" class="p-2"></div></span>
      </div>
      <div class="modal-footer">
        <button type="button" id="renameFile" data-bs-dismiss="modal" class="btn btn-primary">Rename</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="folderCreateModal" tabindex="-1" aria-labelledby="folderCreateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="folderCreateModalLabel">New Folder</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="foldername" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" placeholder="New Folder">
      </div>
      <div class="modal-footer">
        <button type="button" id="createFolder" data-bs-dismiss="modal" class="btn btn-primary">Create</button>
      </div>
    </div>
  </div>
</div>
</main>
</body>
</html>


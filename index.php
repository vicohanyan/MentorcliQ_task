<!DOCTYPE html>
<html>
<head>
    <title>ImageUpload</title>
</head>
<body>
<form action="index.php"  method="post" enctype="multipart/form-data">
    <label>Upload CSV</label>
    <input type="file" name='csv_file'>
    <br/>
    <input type="submit" value="upload">
</form>
</body>
</html>

<?php
ini_set("error_reporting",1);


const FILE_UPLOAD_FOLDER = './storage/';

if(!empty($_FILES['csv_file'])){

    $file_name = $_FILES['csv_file']['name'];
    $file_tmp  = $_FILES['csv_file']['tmp_name'];
    $file_type = $_FILES['csv_file']['type'];
    $file_size = $_FILES['csv_file']['size'];
    $temp_ext  = explode('.', $file_name);
    $file_ext  = strtolower(end($temp_ext));
    if(empty($file_size)){
        echo 'file size is 0 file is empty ';
    }
    if(!file_exists(FILE_UPLOAD_FOLDER)){
        mkdir(FILE_UPLOAD_FOLDER, 0755);
    }
    $newName =  "employees";
    $file_hash = hash_file('md5', FILE_UPLOAD_FOLDER.$newName.'.'.$file_ext);
    $moving = move_uploaded_file($file_tmp, FILE_UPLOAD_FOLDER .$newName.'.'.$file_ext);
    if($moving){
        parsingData();
    }
}else {
    echo  'data is empty';
}


function parsingData(){
    $data = parseCSV(FILE_UPLOAD_FOLDER."employees.xls");

    var_dump($data);
}

function parseCSV(string $path, string $delimiter = ","): array
{
    $file = [];
    if (($handle = fopen($path, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 99999, $delimiter)) !== FALSE) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = trim($data[$i]);
            }
            array_push($file, $data);
        }
        fclose($handle);
    }
    return $file;
}
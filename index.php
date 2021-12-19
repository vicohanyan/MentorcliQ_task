<!DOCTYPE html>
<html lang="en">
<head>
    <title>ImageUpload</title>
</head>
<body>
<form action="index.php" method="post" enctype="multipart/form-data">
    <label>Upload CSV</label>
    <input type="file" name='csv_file'>
    <br/>
    <input type="submit" value="upload">
</form>
</body>
</html>

<?php
ini_set("error_reporting", 1);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

const FILE_UPLOAD_FOLDER = './storage/';

if (!empty($_FILES['csv_file'])) {
    if (downloadFile($_FILES['csv_file'])) { // file is downloaded and can start data analyze
        parsingData();
    }
} else {
    echo 'data is empty';
}
parsingData();

/**
 * Data analyse logic
 * @return array
 */
function parsingData():array
{
    $arrayData = parseCSV(FILE_UPLOAD_FOLDER . "employees.xls");
    $header = $arrayData[0];
    unset($arrayData[0]);
    $arrayData = array_values($arrayData);

    // Got all possible variants by pairs
    $AllVariants = generateAllPossibleVariants($arrayData);
//
//    $finalArray = [];
//    $loopCount = count($AllVariants) / 2;
//    for ($l = 0; $l < $loopCount; $l++) {
//        var_dump($l);
//        $tempData = [];
//        foreach ($AllVariants as $key => $datum) {
//
//            if (!in_array($key,$tempData)) {
//                $finalArray[$l."----"][] = $datum;
//                $tempData[] = $key;
//                unset($AllVariants[$key]);
//            }
//            var_dump($tempData);
//        }
//    }
//    echo "<pre>";
//    print_r($finalArray);
    return [];
}

/**
 * Get data from csv file who not bigger 999999 line
 * @param string $path
 * @param string $delimiter
 * @return array
 */
function parseCSV(string $path, string $delimiter = ","): array
{
    $fileByArray = [];
    if (($handle = fopen($path, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 99999, $delimiter)) !== FALSE) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = trim($data[$i]);
            }
            $fileByArray[] = $data;
        }
        fclose($handle);
    }
    return $fileByArray;
}

/**
 * Generate All possible variants and group it by pair
 * @param array $data
 * @return array
 */
function generateAllPossibleVariants(array $data): array
{
    $AllVariants = [];
    for ($i = 0; $i < count($data); $i++) {
        for ($j = 0; $j < count($data); $j++) {
            if (
                $data[$i][1] != $data[$j][1]
                && !array_key_exists($data[$i][1] . "-" . $data[$j][1], $AllVariants)
                && !array_key_exists($data[$j][1] . "-" . $data[$i][1], $AllVariants)
            ) {
                $AllVariants[$data[$i][1] . "-" . $data[$j][1]] = [$data[$i], $data[$j]];
            }
        }
    }
    return $AllVariants;
}


/**
 * Download file who got from $_FILES superglobal variable
 * @param array $file File who need download from $_FILES superglobal variable
 * @return bool
 */
function downloadFile(array $file): bool
{
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $temp_ext = explode('.', $file_name);
    $file_ext = strtolower(end($temp_ext));
    if (empty($file_size)) {
        echo 'file size is 0 file is empty ';
    }
    if (!file_exists(FILE_UPLOAD_FOLDER)) {
        mkdir(FILE_UPLOAD_FOLDER, 0755);
    }
    $newName = "employees";
    return move_uploaded_file($file_tmp, FILE_UPLOAD_FOLDER . $newName . '.' . $file_ext);
}


$arrayData = ["A", "B", "C", "D","E","F"];
$groupCount = (((count($arrayData)) * ((count($arrayData)) - 1)) / 2) / 2;
$AllVariants = [];

for ($i = 0; $i < count($arrayData); $i++) {
    for ($j = 0; $j < count($arrayData); $j++) {
        if (
            $arrayData[$i] != $arrayData[$j]
            && !array_key_exists($arrayData[$i] . $arrayData[$j], $AllVariants)
            && !array_key_exists($arrayData[$j] . $arrayData[$i], $AllVariants)
        ) {
            $AllVariants[$arrayData[$i] . $arrayData[$j]] = [$arrayData[$i], $arrayData[$j]];
        }
    }
}
$finalArray = [];
var_dump(($AllVariants));
$loopCount = count($AllVariants) / 3;
/** @ToDO fix this part **/
for ($l = 0; $l < $loopCount; $l++) {
    $tempData = "";
    foreach ($AllVariants as $key => $datum) {
        $arrayKeys = str_split($key);
        if(!str_contains($tempData,$arrayKeys[0]) && !str_contains($tempData,$arrayKeys[1])){
            $finalArray[$l][] = $datum;
            $tempData .= $key;
            unset($AllVariants[$key]);
        }
    }
}
var_dump($finalArray);

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
ini_set('max_execution_time', 180);

const FILE_UPLOAD_FOLDER = './storage/';

if (!empty($_FILES['csv_file'])) {
    if (downloadFile($_FILES['csv_file'])) { // file is downloaded and can start data analyze
        $analyzedData = findMaxMatched();
        http_redirect("recommendation.php",$analyzedData);
    }
} else {
    echo 'data is empty';
}

/**
 * Find max matched group
 * @return array
 */
function findMaxMatched(): array
{
    $arrayData = parseCSV(FILE_UPLOAD_FOLDER . "employees.xls");
    // Maybe used from version 2 :)
    // $header = $arrayData[0];
    unset($arrayData[0]);
    $arrayData = array_values($arrayData);

    // Got all possible variants by pairs
    $AllVariants = generateAllPossibleVariants($arrayData);

    // Got generated all possible groups
    $GroupedVariants = generatePossibleGroups($AllVariants);
    $maxMatching = 0;
    $maxMatchingKey = null;
    foreach ($GroupedVariants as $key => $data){
        if($data['matching'] > $maxMatching){
            $maxMatching = $data['matching'];
            $maxMatchingKey = $key;
        }
    }
    if($maxMatchingKey !== null){
        return [$GroupedVariants[$maxMatchingKey]][0];
    }
    return [0];
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
            ) {
                $AllVariants[$data[$i][1] . "-" . $data[$j][1]] = [$data[$i], $data[$j]];
            }
        }
    }
    return $AllVariants;
}

/**
 * Generate possible groups from all possible pairs
 * @param array $AllVariants
 * @return array
 */
function generatePossibleGroups(array $AllVariants):array{
    $finalArray = [];
    $step = 0;
    while (!empty($AllVariants)) {
        $tempData = [];
        foreach ($AllVariants as $key => $variant) {
            if (!in_array($variant[0][1], $tempData) && !in_array($variant[1][1], $tempData)) {
                $matching = 0;
                if($variant[0][2] == $variant[1][2]){
                    $matching += 30;
                }
                if( -5 <= ($variant[0][3] - $variant[1][3]) || 5 >= ($variant[0][3] - $variant[1][3])){
                    $matching += 30;
                }
                if($variant[0][4] == $variant[1][4]){
                    $matching += 40;
                }
                $finalArray[$step]["data"][] = [$variant];
                $finalArray[$step]["matching"] += $matching;
                $tempData[] = $variant[0][1];
                $tempData[] = $variant[1][1];
                if(array_key_first($AllVariants) == $key){
                    unset($AllVariants[$key]);
                }
            }
        }
        $finalArray[$step]["matching"] = $finalArray[$step]["matching"]/ count($finalArray[$step]["data"]);
        $step++;
        if ($step > count($AllVariants)) {
            break;
        }
    }
    return $finalArray;
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

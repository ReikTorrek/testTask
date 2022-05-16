<?php
//Функция вытаскивания текста из лога
function getLogArray() {
    $fp = fopen('C:\xampp\htdocs\2022\test\access.log', "r");
    $line = [];
    if ($fp) {
        while (($buffer = fgets($fp, 4096)) !== false) {
            array_push($line, $buffer);
        }
        if (!feof($fp)) {
            echo "Ошибка: Что - то пошло не так...\n";
        }
        fclose($fp);
        return $line;
    }
    return 0;
}

//Переменные
$stringArray = getLogArray();
$result = [];
$hits = 0;
$unicURL = [];
$traffic = 0;
$stringNumber = count($stringArray);
$responseCodes = [];
$crawler = '';
$crawlers = [];
$jsonResult = [
    'hits:' => 0,
    'urls:' => 0,
    'traffic:' => 0,
    'lines:' => 0,

    'statusCodes:' => [],
    'browsers:' => [],
];
//Регулярные выражения
$pattern = "/(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")/";
$browserPattern = '/msnbot|Google|Yahoo/';
//Блок парсинга массива логов и получения информации.
foreach ($stringArray as $string) {
    preg_match($pattern, $string, $result);
    preg_match($browserPattern, $result[13], $crawler);
    if (!empty($crawler)) {
        array_push($crawlers, $crawler[0]);
    }
    $traffic += (int) $result[11];
    array_push($responseCodes, $result[10]);
    array_push($unicURL, $result[8]);
    $hits += (bool) $result[12];
}
$responseCodes = array_count_values($responseCodes);
$crawlers = array_count_values($crawlers);
$unicURLCount = count(array_unique($unicURL));
$jsonResult = [
    'hits:' => $hits,
    'urls:' => $unicURLCount,
    'traffic:' => $traffic,
    'lines:' => $stringNumber,

    'statusCodes:' => $responseCodes,
    'browsers:' => $crawlers,
];
//Блок вывода информации.
echo json_encode($jsonResult, JSON_HEX_TAG, 3);


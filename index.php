<?php
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

$stringArray = getLogArray(); // Получаем массив строки из лога
$pattern = "/(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")/"; //Регулярка, по которой будем делить лог
$result = []; //Массив, в котором будет разделённый лог
$hits = 0; //Количество хитов
$unicURL = []; //Уникальный URL
$traffic = 0; //размер траффика
$stringNumber = count($stringArray); //Количество строк в файле
$responseCodes = []; //Массив кодов ответов
$browserPattern = '/msnbot|Google|Yahoo/'; //Регулярка, по которой мы ищем условных ботов поисковиков.
$crawler = ''; //Переменная, в которую мы будем получать браузер.
$crawlers = []; //Массив, в котором будут записаны все браузеры
$jsonResult = [
    'hits:' => 0,
    'urls:' => 0,
    'traffic:' => 0,
    'lines:' => 0,

    'statusCodes:' => [],
    'browsers:' => [],
]; //массив итогового результата.
/*
['ip'] = $result [1];
['identity'] = $result [2];
['user'] = $result [3];
['date'] = $result [4];
['time'] = $result [5];
['timezone'] = $result[6];
['method'] = $result [7];
['path'] = $result[8];
['protocol'] = $result[9];
['status'] = $result[10];
['bytes'] = $result[11];
['referer'] = $result[12];
['agent'] = $result[13];
*/
foreach ($stringArray as $string) { // В цикле по очереди разводим массив строк по одной, подставляем под регулярку и выводим в $result, чтобы получить распарсенную строку.
    preg_match($pattern, $string, $result);
    preg_match($browserPattern, $result[13], $crawler); //Парсим строку браузера на наличие ботов поисковиков.
    if (!empty($crawler)) {
        array_push($crawlers, $crawler[0]); //если есть - пишем в массив.
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

echo json_encode($jsonResult, JSON_HEX_TAG, 3);


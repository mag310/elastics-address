<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.10.18
 * Time: 15:05
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/fias.php';
require __DIR__ . '/Elastic.php';

(new josegonzalez\Dotenv\Loader(__DIR__ . '/.env'))->parse()->putenv(true)->define();

$batchSize = 10000;

for ($i = 1000000; $i < 3000000; $i += $batchSize) {
    try {
        $houses = getHouseAddress(74, $i, $batchSize);
    } catch (Exception $e) {
        echo 'Ошибка получения списка домов' . PHP_EOL;
        echo $e->getMessage();
        die;
    }

// здесь мы каким-то образом используем соединение
    sendBulk($houses);
}


<?php

use Elasticsearch\ClientBuilder;


function getClient()
{
    $hosts = [
        '10.80.32.67:9200',         // IP + Port
    ];

    $clientBuilder = ClientBuilder::create();

    $clientBuilder->setHosts($hosts);
    return $clientBuilder->build();

}

function sendBulk($batches)
{
    foreach ($batches as $row) {
        $params['body'][] = [
            'index' => [
                '_index' => 'fias_rus',
                '_type' => 'house',
                '_id' => $row['uid'],
            ]
        ];

        $params['body'][] = ['id' => $row['uid'], 'name' => $row['address']];
    }

    getClient()->bulk($params);

    echo count($params['body']) . PHP_EOL;
}
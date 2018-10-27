<?php
require __DIR__ . '/vendor/autoload.php';

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

//
$param = $_GET['name'];
var_dump($param);
$json = '{
    "query":{
        "bool":{
            "must":[
                {
                    "query_string":{
                        "query":"' . $param . '",
                        "analyze_wildcard":true,
                        "default_field":"name",
                        "boost":20.0
                    }
                }
            ],
            "filter":[],
            "should":[],
            "must_not":[]
        }
    },
    "highlight":{
        "pre_tags":["<b style=\"color:red;\">"],
        "post_tags":["</b>"],
        "fields":{
            "*":{}
        },
        "fragment_size":2147483647
    },
    "sort": [
        { "_score": { "order": "desc" }}
    ]
}';
//
//$json = '{
//    "query":{
//        "bool":{
//            "must":[
//                {
//                    "query_string":{
//                        "query":"' . $param . '",
//                        "analyze_wildcard":true,
//                        "default_field":"*",
//                    }
//                }
//            ],
//            "filter":[],
//            "should":[],
//            "must_not":[]
//        }
//    },
//    "highlight":{
//        "pre_tags":["<b style=\"color:red;\">"],
//        "post_tags":["</b>"],
//        "fields":{
//            "*":{}
//        },
//        "fragment_size":2147483647
//    }
//}';

$params = [
    'index' => 'fias_rus',
    'type' => 'house',
    'body' => $json
];

try {
    $client = getClient();
    $results = $client->search($params);
} catch (Exception $e) {
    echo '<pre>';
    echo $e->getMessage();
    echo '</pre>';
}
?>

<h2>Найдено: <?=$results['hits']['total']?></h2>
<?php
foreach ($results['hits']['hits'] as $k=>$row):
?>
<h3><?=$row['_source']['name']?></h3>
<pre>
    <?php var_dump($row); ?>
</pre>
    <br/><br/>
<?php endforeach; ?>
<?php
//var_dump($results);
//var_dump($client->transport->lastConnection->getLastRequestInfo()['response']);

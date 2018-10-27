<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.10.18
 * Time: 19:53
 */

/**
 * @param string $uid
 * @param Redis $redis
 * @param PDO $db
 * @return array
 * @throws Exception
 */
function getStreet($uid, $redis, $db): array
{
    if ($res = $redis->get($uid)) {
        $res = json_decode($res, JSON_OBJECT_AS_ARRAY);
        if (is_array($res)) {
            return $res;
        }
    }

    $query = "SELECT PARENTGUID, SHORTNAME, OFFNAME, AOLEVEL FROM addrob WHERE AOGUID='{$uid}'";
    $addrObj = $db->query($query, PDO::FETCH_ASSOC);
    if (!$addrObj) {
        throw new Exception('Ошибка запроса в БД' . PHP_EOL . $db->errorCode() . ' : ' . var_export($db->errorInfo(), true));
    }
    $addrObj = $addrObj->fetch();
    if (empty($addrObj)) {
        return [
            'aoname' => 'Неизвестно',
            'aoguid' => $uid,
        ];
    }

    $name = $addrObj['SHORTNAME'] . '. ' . $addrObj['OFFNAME'];
    $res = [
        'aoname' => $name,
        'aoguid' => $uid,
        'addr' . $addrObj['AOLEVEL'] => $name,
        'uid' . $addrObj['AOLEVEL'] => $uid
    ];

    if ($addrObj['PARENTGUID']) {
        $parrent = getStreet($addrObj['PARENTGUID'], $redis, $db);
        $res['aoname'] = $parrent['aoname'] . ', ' . $name;

        $res = array_merge($parrent, $res);
    }

    echo 'Запрос в базу' . PHP_EOL;
    $redis->set($uid, json_encode($res));

    return $res;
}

/**
 * @param int $region
 * @param int $start
 * @param int $limit
 * @return array
 * @throws Exception
 */
function getHouseAddress($region, $start, $limit = 1000)
{
    $res = [];

    $redis = new Redis();
    if (!$redis->connect('redis', 6379)) {
        throw new Exception('Ошибка подключения к Redis');
    }
    $redis->select(0);
    $db = new PDO('mysql:host=mysql;dbname=fias;charset=UTF8', "fias", "password");
    $query = "SELECT HOUSEGUID, AOGUID, HOUSENUM, BUILDNUM, STRUCNUM FROM house LIMIT {$start}, {$limit}";

    if (!$sth = $db->query($query, PDO::FETCH_ASSOC)) {
        throw new Exception('Ошибка запроса в БД' . PHP_EOL . $db->errorCode() . ' : ' . var_export($db->errorInfo(), true));
    }
    foreach ($sth as $house) {
        if (!empty($house['HOUSENUM'])) {
            $buildNum = 'д.' . $house['HOUSENUM'] . $house['BUILDNUM'] . ($house['STRUCNUM'] ? '/' . $house['STRUCNUM'] : '');
        } elseif (!empty($house['STRUCNUM'])) {
            $buildNum = "стр. " . $house['STRUCNUM'];
        } else {
            $buildNum = '';
        }
        $street = getStreet($house['AOGUID'], $redis, $db);

        $house = [
            'uid' => $house['HOUSEGUID'],
            'address' => $street['aoname'] . ', ' . $buildNum,
            'addr8' => $buildNum,
            'uid8' => $house['HOUSEGUID'],
        ];

        $house = array_merge($street, $house);
        unset($house['aoname']);
        unset($house['aoguid']);
        $res[] = $house;
    }
    $redis->close();
    $sth = null;
    $db = null;
    return $res;

}
<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.10.18
 * Time: 23:13
 */

/**
 * @param PDO $db
 * @param string $tableName
 * @param array $columnsList
 * @param array $data
 * @return bool
 */
function batchInsert($db, $tableName, $columnsList, $data)
{
    $columns = '`' . implode('`, `', $columnsList) . '`';
    $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ";

    $insertQuery = array();
    $insertData = array();
    $n = 0;
    foreach ($data as $row) {
        $paramList = [];
        foreach ($row as $colName => $value) {
            $parName = $colName . $n;
            $paramList[] = ':' . $parName . '';
            $insertData[$parName] = $value;
        }
        $insertQuery[] = '(' . implode(', ', $paramList) . ')';
        $n++;
    }

    try {
        if (!empty($insertQuery)) {
            $sql .= implode(', ', $insertQuery);
            $stmt = $db->prepare($sql);

            if (!$stmt->execute($insertData)) {
                echo $stmt->errorCode() . ': ' . var_export($stmt->errorInfo(), true) . PHP_EOL;
                var_dump($data);
                return false;
            }

            return true;
        }
    } catch (Exception $e) {
        echo $e->getCode() . ': ' . $e->getMessage() . PHP_EOL;
    }
    return true;
}

$db = new PDO('mysql:host=mysql;dbname=fias;charset=UTF8', "fias", "password");
//$sth = $db->query('SHOW tables', PDO::FETCH_ASSOC);
//var_dump($sth->fetch());

$dbase = dbase_open('./fias/ADDROB50.DBF', 0);

if (!$dbase) {
    throw new Exception('Не смогли подключиться к dbase');
}
// чтение некотрых данных
$info = dbase_get_header_info($dbase);

$count = dbase_numrecords($dbase);

$data = [];

for ($i = 1; $i < $count; $i++) {
    $row = dbase_get_record_with_names($dbase, $i);
    $row = array_map(function ($value) {
        $res = iconv('CP866', 'UTF-8', $value);
        $res = trim($res);
        return $res;
    }, $row);

    if ($row['LIVESTATUS'] == '1' && $row['ACTSTATUS'] == '1') {
        $data[] = [
            'AOGUID' => $row['AOGUID'],
            'REGIONCODE' => $row['REGIONCODE'],
            'OFFNAME' => $row['OFFNAME'],
            'SHORTNAME' => $row['SHORTNAME'],
            'AOLEVEL' => (int)$row['AOLEVEL'],
            'PARENTGUID' => $row['PARENTGUID'],
        ];
    }
    if (count($data) % 1000 == 0) {
        $res = batchInsert(
            $db,
            'addrob',
            ['AOGUID', 'REGIONCODE', 'OFFNAME', 'SHORTNAME', 'AOLEVEL', 'PARENTGUID'],
            $data
        );
        echo 'Вставили 1000 строк' . PHP_EOL;
        $data = [];
    }
}
if (count($data) > 0) {
    $res = batchInsert(
        $db,
        'addrob',
        ['AOGUID', 'REGIONCODE', 'OFFNAME', 'SHORTNAME', 'AOLEVEL', 'PARENTGUID'],
        $data
    );
}

dbase_close($dbase);

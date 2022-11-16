<?php
const WRITE_SQL_FILE = true;
const DATABASE_INI = '../database.ini';
const SQL_FOLDER = '../sql';

function connect_db($file) {
    $db = parse_ini_file($file);
    $connection = sprintf('pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s;sslmode=%s',
        $db['host'],
        $db['port'],
        $db['dbname'],
        $db['user'],
        $db['password'],
        $db['sslmode']);

    $pdo = new \PDO($connection);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = connect_db(DATABASE_INI);

$raw = file_get_contents('php://input');
if (!$raw) {
    http_response_code(400); // Bad Request (no payload)
    return;
}

$json = json_decode($raw, true);
if ($json == NULL) {
    http_response_code(400); // Bad Request (invalid json)
    return;
}

$now = date('Y-m-d H:i:s');
$sensor_data_values = array();
foreach ($json['sensordatavalues'] as $measure) {
    $sensor_data_values[$measure['value_type']] = $measure['value'];
}

$statement = 'INSERT INTO measurement (time, sensor_id, firmware, bmp_pressure, bmp_temperature, heca_humidity, heca_temperature, pm_10, pm_25, sht_humidity, sht_temperature, wifi_signal) VALUES(:time, :sensor_id, :firmware, :bmp_pressure, :bmp_temperature, :heca_humidity, :heca_temperature, :pm_10, :pm_25, :sht_humidity, :sht_temperature, :wifi_signal) ON CONFLICT (time, sensor_id) DO NOTHING';
$insert = $pdo->prepare($statement);
$insert->bindValue(':time',             $now,                                     PDO::PARAM_STR);
$insert->bindValue(':sensor_id',        $json['esp8266id'],                       PDO::PARAM_INT);
$insert->bindValue(':firmware',         $json['software_version'],                PDO::PARAM_STR);
$insert->bindValue(':bmp_pressure',     $sensor_data_values['BMP_pressure'],      PDO::PARAM_INT);
$insert->bindValue(':bmp_temperature',  $sensor_data_values['BMP_temperature'],   PDO::PARAM_INT);
$insert->bindValue(':heca_humidity',    $sensor_data_values['HECA_humidity'],     PDO::PARAM_INT);
$insert->bindValue(':heca_temperature', $sensor_data_values['HECA_temperature'],  PDO::PARAM_INT);
$insert->bindValue(':pm_10',            $sensor_data_values['SDS_P1'],            PDO::PARAM_INT);
$insert->bindValue(':pm_25',            $sensor_data_values['SDS_P2'],            PDO::PARAM_INT);
$insert->bindValue(':sht_humidity',     $sensor_data_values['SHT3X_humidity'],    PDO::PARAM_INT);
$insert->bindValue(':sht_temperature',  $sensor_data_values['SHT3X_temperature'], PDO::PARAM_INT);
$insert->bindValue(':wifi_signal',      $sensor_data_values['signal'],            PDO::PARAM_INT);

if (WRITE_SQL_FILE) {
    $file = SQL_FOLDER . '/sensor-' . date('Y-m-d') . '.sql';
    $fd = fopen($file, 'a');
    if (!$fd) {
        http_response_code(500); // Internal Server Error
        return;
    }

    $sql = $statement;
    $sql = str_replace(':time',             "'" . $now . "'",                                     $sql);
    $sql = str_replace(':sensor_id',        "'" . $json['esp8266id'] . "'",                       $sql);
    $sql = str_replace(':firmware',         "'" . $json['software_version'] . "'",                $sql);
    $sql = str_replace(':bmp_pressure',     "'" . $sensor_data_values['BMP_pressure'] . "'",      $sql); 
    $sql = str_replace(':bmp_temperature',  "'" . $sensor_data_values['BMP_temperature'] . "'",   $sql); 
    $sql = str_replace(':heca_humidity',    "'" . $sensor_data_values['HECA_humidity'] . "'",     $sql); 
    $sql = str_replace(':heca_temperature', "'" . $sensor_data_values['HECA_temperature'] . "'",  $sql); 
    $sql = str_replace(':pm_10',            "'" . $sensor_data_values['SDS_P1'] . "'",            $sql); 
    $sql = str_replace(':pm_25',            "'" . $sensor_data_values['SDS_P2'] . "'",            $sql); 
    $sql = str_replace(':sht_humidity',     "'" . $sensor_data_values['SHT3X_humidity'] . "'",    $sql); 
    $sql = str_replace(':sht_temperature',  "'" . $sensor_data_values['SHT3X_temperature'] . "'", $sql);
    $sql = str_replace(':wifi_signal',      "'" . $sensor_data_values['signal'] . "'",            $sql); 

    if (!fwrite($fd, $sql . ';' . PHP_EOL)) {
        http_response_code(500); // Internal Server Error
        return;
    }
    if(!fclose($fd)) {
        http_response_code(500); // Internal Server Error
        return;
    }
}
$insert->execute();

http_response_code(201); // Created
?>


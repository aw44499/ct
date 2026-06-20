<?php
// CORS 跨域支持
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataFile = __DIR__ . '/Key/key.json';
date_default_timezone_set('PRC');

function returnJson($code, $message, $usertime = null) {
    $result = [
        'code' => $code,
        'message' => $message,
        'usertime' => $usertime
    ];
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}

$string = isset($_REQUEST['string']) ? trim($_REQUEST['string']) : null;
$id     = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : null;

if (empty($string) || empty($id)) {
    returnJson(400, '参数缺失');
}

if (!file_exists($dataFile)) {
    returnJson(500, '数据文件不存在');
}

$jsonContent = file_get_contents($dataFile);
$rows = json_decode($jsonContent, true);

if (!is_array($rows)) {
    returnJson(500, '数据文件格式错误');
}

$targetRow = null;
$targetIndex = -1;

foreach ($rows as $index => $row) {
    if (trim($row['string']) === $string) {
        $targetRow = $row;
        $targetIndex = $index;
        break;
    }
}

if ($targetRow === null) {
    returnJson(404, '卡密错误');
}

$now = time();
$usertime = $targetRow['usertime'] ?? 'null';
$rowId = $targetRow['id'] ?? 'null';
$days = intval($targetRow['time'] ?? 0);

$loginSucc = false;
$returnUsertime = $usertime;

if ($usertime === 'null' || $usertime === null) {
    $rows[$targetIndex]['id'] = $id;
    $expireTime = strtotime("+{$days} days", $now);
    $returnUsertime = date('Y-m-d H:i:s', $expireTime);
    $rows[$targetIndex]['usertime'] = $returnUsertime;
    $loginSucc = true;
} else {
    $userTimeStamp = strtotime($usertime);
    if ($userTimeStamp === false || $userTimeStamp <= $now) {
        returnJson(403, '卡密失效', $usertime);
    }
    if ($rowId !== $id) {
        returnJson(401, '请在原设备登录', $usertime);
    }
    $loginSucc = true;
}

if ($loginSucc) {
    if (file_put_contents($dataFile, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
        returnJson(500, '数据写入失败', $returnUsertime);
    }
    returnJson(200, '登录成功', $returnUsertime);
} else {
    returnJson(500, '登录失败', $returnUsertime);
}

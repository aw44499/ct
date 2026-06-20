<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>卡密查询</title></head>
<body>
<h1>卡密状态查询</h1>
<form method="get" action="">
<input type="text" name="key" placeholder="输入卡密">
<button type="submit">查询</button>
</form>
<?php
if (isset($_GET['key'])) {
    $keyFile = './Key/key.json';
    $keys = json_decode(file_get_contents($keyFile), true) ?: [];
    $found = false;
    foreach ($keys as $k) {
        if ($k['string'] === $_GET['key']) {
            echo '<p>卡密: ' . htmlspecialchars($k['string']) . '</p>';
            echo '<p>天数: ' . $k['time'] . '</p>';
            echo '<p>状态: ' . ($k['usertime'] ? '已激活' : '未激活') . '</p>';
            $found = true;
            break;
        }
    }
    if (!$found) echo '<p>卡密不存在</p>';
}
?>
</body>
</html>

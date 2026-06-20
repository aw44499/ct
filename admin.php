<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=utf-8");

$keyFile = './Key/key.json';
$txtFile = './Key/txt.json';
$adminFile = './Key/admin.json';

if (!is_dir('./Key')) mkdir('./Key', 0755, true);
if (!file_exists($keyFile)) file_put_contents($keyFile, json_encode([]));
if (!file_exists($txtFile)) file_put_contents($txtFile, json_encode(["标题" => "", "内容" => ""]));
if (!file_exists($adminFile)) file_put_contents($adminFile, json_encode(["username" => "admin", "password" => "admin123"], JSON_PRETTY_PRINT));

function getKeys() {
    global $keyFile;
    $data = file_get_contents($keyFile);
    return json_decode($data, true) ?: [];
}

function saveKeys($keys) {
    global $keyFile;
    file_put_contents($keyFile, json_encode($keys, JSON_PRETTY_PRINT));
}

function generateRandomStr() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    for ($i = 0; $i < 9; $i++) {
        $str .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $str;
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $adminConfig = json_decode(file_get_contents($adminFile), true);
    if ($username === $adminConfig['username'] && $password === $adminConfig['password']) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "用户名或密码错误"]);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($isLoggedIn && isset($_POST['action']) && $_POST['action'] == 'add_key') {
    $prefix = trim($_POST['prefix'] ?? '');
    $days = trim($_POST['days'] ?? '1');
    $count = intval($_POST['count'] ?? 1);

    $keys = getKeys();
    $newKeys = [];
    for ($i = 0; $i < $count; $i++) {
        $string = $prefix . generateRandomStr();
        $keys[] = [
            "string" => $string,
            "time" => $days,
            "usertime" => null,
            "id" => null
        ];
        $newKeys[] = $string;
    }
    saveKeys($keys);
    echo json_encode(["status" => "success", "keys" => $newKeys]);
    exit;
}

$keys = getKeys();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>卡密管理</title></head>
<body>
<h1>卡密管理后台</h1>
<?php if (!$isLoggedIn): ?>
<form method="post">
<input type="hidden" name="action" value="login">
用户名: <input type="text" name="username"><br>
密码: <input type="password" name="password"><br>
<button type="submit">登录</button>
</form>
<?php else: ?>
<a href="?action=logout">退出</a>
<h2>添加卡密</h2>
<form method="post">
<input type="hidden" name="action" value="add_key">
前缀: <input type="text" name="prefix" value="永久"><br>
天数: <input type="number" name="days" value="-1"><br>
数量: <input type="number" name="count" value="1"><br>
<button type="submit">生成</button>
</form>
<h2>卡密列表</h2>
<table border="1">
<tr><th>卡密</th><th>天数</th><th>状态</th></tr>
<?php foreach ($keys as $key): ?>
<tr>
<td><?php echo htmlspecialchars($key['string']); ?></td>
<td><?php echo $key['time']; ?></td>
<td><?php echo $key['usertime'] ? '已激活' : '未激活'; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>

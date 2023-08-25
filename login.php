<?php

include 'init.php';


//initialization
$crypter = Crypter::init();
$privatekey = readFileData("keyLmao/PrivateKey.prk");

function tokenResponse($data){
    global $crypter, $privatekey;
    $data = toJson($data);
    $datahash = sha256($data);
    $acktoken = array(
        "Data" => profileEncrypt($data, $datahash),
        "Sign" => toBase64($crypter->signByPrivate($privatekey, $data)),
        "Hash" => $datahash
    );
    return toBase64(toJson($acktoken));
}

//token data
$token = fromBase64($_POST['token']);
$tokarr = fromJson($token, true);

//Data section decrypter
$encdata = $tokarr['Data'];
$decdata = trim($crypter->decryptByPrivate($privatekey, fromBase64($encdata)));
$data = fromJson($decdata);

//Hash Validator
$tokhash = $tokarr['Hash'];
$newhash = sha256($encdata);

if (strcmp($tokhash, $newhash) == 0) {
    PlainDie();
}

if($maintenance){
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Máy chủ đang bảo trì",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}


$uname = $data["uname"];
if($uname == null || preg_match("([A-Z0-9]+)", $uname) === 0){
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Invalid Username",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}

//Password Validator
$pass = $data["pass"];
if($pass == null || !preg_match("([a-zA-Z0-9]+)", $pass) === 0){
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Invalid Password",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}

$tok = $data["tok"];
$query = $conn->query("SELECT * FROM `tokens` WHERE `Username` = '".$uname."' AND `Password` = '".$pass."'");
if($query->num_rows < 1){
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Tên đăng nhập hoặc mật khẩu không chính xác!",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}

$res = $query->fetch_assoc();
if($res["StartDate"] == NULL){
    $query = $conn->query("UPDATE `tokens` SET `StartDate` = CURRENT_TIMESTAMP WHERE `Username` = '".$uname."' AND `Password` = '".$pass."'");
}

if($res["UID"] == NULL){
    $query = $conn->query("UPDATE `tokens` SET `UID` = '".$data["cs"]."' WHERE `Username` = '".$uname."' AND `Password` = '".$pass."'");
} else if($res["UID"] != $data["cs"]) {
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Thiết bị đã thay đổi",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}


if($res["EndDate"] < date('Y-m-d H:i')){
    $ackdata = array(
        "Status" => "Failed",
        "MessageString" => "Key đã hết hạn",
        "SubscriptionLeft" => "0"
    );
    PlainDie(tokenResponse($ackdata));
}

$endDateString = $res["EndDate"];
$endDate = new DateTime($endDateString);

$currentDate = new DateTime();
// $currentDate->setTime(0, 0, 0);

$interval = $currentDate->diff($endDate);
$daysRemaining = $interval->days;

$gameToRetrieve = "Free Fire";
$sql = "SELECT hosts, features FROM game_hosts WHERE game = '$gameToRetrieve'";
$result = $conn->query($sql);
$hostsJson;
$ftJsons;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hostsJson = $row['hosts'];
    $ftJsons = $row['features'];
}

$ackdata = array(
    "Status" => "Success",
    "MessageString" => "",
    "Username" => $uname,
    "StartDate" =>  $res["StartDate"],
    "EndDate" =>  $res["EndDate"],
    "Expiry" => "$daysRemaining",
    "TokenLog" => "$tok",
    "SubscriptionLeft" => "Thành công",
    "Host" => toBase64($hostsJson),
    "Feature" => toBase64($ftJsons),
);

echo tokenResponse($ackdata);

?>

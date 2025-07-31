<?php
/*
BlitZlog api[login] v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$api = true;
$admin = true;
$returnText = [];
require("../../src/head.php");
require("../../src/sqlite.php");
require("../../src/login_check.php");

header("Content-Type: application/json");

if(isset($_POST["check"])) {
	if(isset($_SESSION["blog_manager_login"])) {
		$responce_code = 200;
		$return = true;
	} else {
		$responce_code = 400;
		$return = false;
	}
} else {
	$email = $_POST["email"];
	$pswd = $_POST["pswd"];
	if(isset($_SESSION["blog_manager_login"])) {
		$responce_code = 303;
		$return = "Already logged in.";
	} elseif(empty($email) || empty($pswd)) {
		$responce_code = 400;
		$return = "Email address or password not entered.";
	} else {
		try {
			$stmt = $db->prepare("SELECT id,email,name,pswd FROM users WHERE email = :email");
			if(!$stmt) {
				throw new Exception("Prepare failed");
			}
			if(!$stmt->bindValue(":email", $email)) {
				throw new Exception("Bind failed");
			}
			if(!$result = $stmt->execute()) {
				throw new Exception("Execute failed");
			}
			$userInfo = $result->fetchArray(SQLITE3_ASSOC);
			if(!$userInfo || !password_verify($pswd,$userInfo["pswd"])) {
				$responce_code = 401;
				$return = "Incorrect email address or password.";
			} else {
				$referer_header = urldecode($_SERVER["HTTP_REFERER"] ?? "");
				$remoteUserIp = $_SERVER["REMOTE_ADDR"];
				$remote_access = remoteAccessCheck($referer_header);
				if($remote_access["allow"] === false) {
					$responce_code = 403;
					$return = "I can't login to BlitZlog.";
				} else {
					$date = date("Ymd");
					$csv_name = date("Ymd_His");
					$time = date("Y-m-d H:i:s");
					if($remote_access["setting"] === 1 || $remote_access["allow"] === true) {
						$_SESSION["blog_manager_login"] = "$userID,$remoteUserIp";
						file_put_contents($documentRoot.$ps."data/analyze/login/$date.csv","$time,$referer_header,$remoteUserIp\n",FILE_APPEND);
						$return = "BlitZlog login successful.";
						$responce_code = 200;
					} else {
						file_put_contents($documentRoot.$ps."data/info/$csv_name.csv",<<<eof
						管理画面操作ではないログインが検出されました。設定により、ブロックされています。
						【詳細情報】
						IPアドレス：$remoteUserIP
						アクセス元：$referer_header
						日時：$time
						%login_action%
						eof);
						$responce_code = 403;
						$return = "BlitZlog login has been blocked.";
					}
				}
			}
		} catch(Exception $e) {
			$responce_code = 400;
			$return = "Failed to retrieve from database.";
		}
	}
}

if(empty($return)) {
	$responce_code = 400;
	$return = "No message available.";
}
echo json_encode([
	"code" => $responce_code ?? 200,
	"message" => $return
],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

function remoteAccessCheck($referer) {
    global $db,$host,$ps,$documentRoot;

    $refererOK1 = [
        $host.$ps."admin/login",
        $host.$ps."admin/login/",
    ];
    $refererOK2 = [
        $host,
        "https://sagashi0120.cloudfree.jp/blitzlog/drive/auth",
    ];
    try {
        $stmt = $db->prepare("SELECT remote_access,remote_setting FROM setting");
        $result = $stmt->execute();
        $setting = $result->fetchArray(SQLITE3_ASSOC);
        $result->finalize();
        if($setting === false) {
            error_log("Remote access settings (id=1) not found in DB. Denying access.");
            return [
				"allow" => false,
				"setting" => false
			];
        }
        $remote_mode = (int)$setting["remote_access"];
        $remote_detail_setting = (int)$setting["remote_setting"];
        $is_access_allowed = false;
        switch($remote_mode) {
            case 1:
                if(in_array($referer,$refererOK1)) {
                    $is_access_allowed = true;
                } else {
                    $is_access_allowed = false;
                }
                break;
            case 2:
                if(in_array($referer,$refererOK1)) {
                	$is_access_allowed = true;
				} elseif(in_array($referer,$refererOK2)) {
                    $is_access_allowed = 1;
                } else {
                    $is_access_allowed = false;
                }
                break;
            case 3:
				if(in_array($referer,$refererOK1)) {
                	$is_access_allowed = true;
				} else {
					$is_access_allowed = 1;
				}
                break;
            default:
                error_log("Undefined remote_access mode in remoteAccessCheck: ".$remote_mode);
                $is_access_allowed = false;
                break;
        }
    } catch(Exception $e) {
        error_log("Exception in remoteAccessCheck: ".$e->getMessage());
        return [
			"allow" => false,
			"setting" => false
		];
    }
	return [
		"allow" => $is_access_allowed,
		"setting" => $remote_detail_setting
	];
}
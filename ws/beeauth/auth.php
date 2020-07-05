<?php
    class BeeAuth {
        private $secret;
        public function __construct() {
            $this->secret = "do your secret ehre";
        }
        private function Base64UrlEncode($t) { return str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($t)); }
        private function GenerateJWTToken($userid, $rolebits) {
            $header = json_encode([
                "typ" => "JWT",
                "alg" => "HS256"
            ]);
            $payload = json_encode([
                "userid" => $userid,
                "rolebits" => $rolebits,
                "created" => time()
            ]);
            $base64UrlHeader = $this->Base64UrlEncode($header);
            $base64UrlPayload = $this->Base64UrlEncode($payload);
            $signature = hash_hmac("sha256", "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
            $base64UrlSignature = $this->Base64UrlEncode($signature);
            return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        }
        private function GetAuthHeader() {
            if(isset($_SERVER["Authorization"])) { return trim($_SERVER["Authorization"]); }
            if(isset($_SERVER["HTTP_AUTHORIZATION"])) { return trim($_SERVER["HTTP_AUTHORIZATION"]); }
            return "";
        }
        public function ValidateJWTToken($token) {
            $tokenParts = explode(".", $token);
            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];
            $payloadObj = json_decode($payload);

            $base64UrlHeader = $this->Base64UrlEncode($header);
            $base64UrlPayload = $this->Base64UrlEncode($payload);
            $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
            $base64UrlSignature = $this->Base64UrlEncode($signature);

            return [
                "valid" => ($base64UrlSignature === $signatureProvided),
                "expired" => ((time() - $payloadObj->created) > 28800), // 8 hours
                "userid" => $payloadObj->userid,
                "rolebits" => $payloadObj->rolebits
            ];
        }
        public function LoginJWT($username, $password) {
            $pdo = new PDO("mysql:host=localhost;dbname=beeaccount", "root", "porridge");
            $q = $pdo->prepare("SELECT password, rolebits, id FROM users WHERE username = :u");
            $q->execute(["u" => $username]);
            $tbl = $q->fetchAll();
            if(count($tbl) !== 1) { return ["success"=>false, "result"=>"User not found."]; }
            $row = $tbl[0];
            if(!password_verify($password, $row["password"])) {
                // TODO: failed login attempts bullshit
                return ["success"=>false, "result"=>"Incorrect password."];
            }
            return [
                "success"=>true,
                "token" => $this->GenerateJWTToken($row["id"], $row["rolebits"])
            ];
        }
        public function ValidateJWT() {
            $auth = $this->GetAuthHeader();
            if($auth === "") { header("HTTP/1.1 401 Unauthorized"); exit; }
            if(preg_match("/Bearer\s((.*)\.(.*)\.(.*))/", $auth, $matches)) {
                $token = $matches[1];
                $resp = $this->ValidateJWTToken($token);
                if(!$resp["valid"] || $resp["expired"]) { header("HTTP/1.1 401 Unauthorized"); exit; }
            } else { header("HTTP/1.1 401 Unauthorized"); exit; }
        }
        private function WipeSessionAndExit() {
            $_SESSION = array();
            session_destroy();
            http_response_code(401);
            exit;
        }
        public function ValidateSession() {
            if(!isset($_SESSION["auth"])) { $this->WipeSessionAndExit(); }
            if($_SESSION["auth"] === false) { $this->WipeSessionAndExit(); }
            #$expired = (time() - $SESSION["created"]) > 28800; // 8 hours
            #if($expired) { $this->WipeSessionAndExit(); }
        }
        public function Login($username, $password) {
            $pdo = new PDO("mysql:host=localhost;dbname=beeaccount", "username", "password");            

            $q = $pdo->prepare("SELECT password, rolebits, id FROM users WHERE username = :u");
            $q->execute(["u" => $username]);
            $tbl = $q->fetchAll();
            if(count($tbl) !== 1) { return ["success"=>false, "result"=>"User not found."]; }
            $row = $tbl[0];
            if(!password_verify($password, $row["password"])) {
                // TODO: failed login attempts bullshit
                return ["success"=>false, "result"=>"Incorrect password."];
            }
            session_start();
            $_SESSION["auth"] = true;
            $_SESSION["created"] = time();
            $_SESSION["username"] = $username;
            $_SESSION["userid"] = $row["id"];
            $_SESSION["rolebits"] = $row["rolebits"];
            return ["success"=>true];
        }
    }
?>
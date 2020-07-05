<?php
    final class Db {
        protected static $dbInstance;
        public static function factory(){
            if(!self::$dbInstance){ 
                self::$dbInstance = new PDO("mysql:host=localhost;dbname=sonic", "username", "password");
            }
            self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$dbInstance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return self::$dbInstance;
        }
    }
    class SQLHelper {
        private $pdo;
        public function __construct() { $this->pdo = Db::factory(); }
        public function BeginTransaction() { $this->pdo->beginTransaction(); }
        public function CommitTransaction() { $this->pdo->commit(); }
        public function RollbackTransaction() { $this->pdo->rollBack(); }
        public function DoMultipleInsert($left, $arr, $query) {
            if(count($arr) === 0) { return; }
            $params = ["a" => $left];
            $sql = [];
            foreach($arr as $k=>$v) {
                $params["b$k"] = $v;
                $sql[] = "(:a, :b$k)";
            }
            $q = $this->pdo->prepare($query.implode(", ", $sql));
            $q->execute($params);
        }
        public function CreateInClauseFromArray($arr) {
            $params = [];
            $sql = [];
            foreach($arr as $k=>$v) {
                $params["l$k"] = $v;
                $sql[] = ":l$k";
            }
            return [
                "paramsObj" => $params,
                "inClause" => implode(", ", $sql)
            ];
        }
        public function GetStringArray($sql, $params, $column) {
            $dt = $this->GetDataTable($sql, $params);
            return array_map(function($e) use ($column) { return $e[$column]; }, $dt);
        }
        public function GetIntArray($sql, $params, $column) {
            $dt = $this->GetDataTable($sql, $params);
            return array_map(function($e) use ($column) { return intval($e[$column]); }, $dt);
        }
        public function GetDataTable($sql, $params) {
            $q = $this->pdo->prepare($sql);
            $q->execute($params);
            return $q->fetchAll();
        }
        public function GetIntValue($sql, $params) {
            $q = $this->pdo->prepare($sql);
            $q->execute($params);
            $tbl = $q->fetchAll(PDO::FETCH_NUM);
            if(count($tbl) !== 1) { return 0; }
            return intval($tbl[0][0]);
        }
        public function ExecuteNonQuery($sql, $params) {
            $q = $this->pdo->prepare($sql);
            $q->execute($params);
        }
        public function InsertAndReturnID($sql, $params) {
            $q = $this->pdo->prepare($sql);
            $q->execute($params);
            return intval($this->pdo->lastInsertId());
        }
    }
?>
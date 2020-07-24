<?php
    session_start();
    require_once(__DIR__."/beeauth/auth.php");
    require_once(__DIR__."/helpers.php");
    require_once(__DIR__."/GregwarCaptcha/CaptchaBuilder.php");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");
	header("Content-Type: application/json");
	if(!isset($_GET["function"])) { echo "{\"success\": false}"; exit; }
	class WebServiceMethods {
		private $sql;
		public function __construct() { $this->sql = new SQLHelper(); }
		public function Fail() { echo "{\"success\": false}"; exit; }
		public function Test() { echo json_encode(["success" => true, "args" => func_get_args()]); }
        
        // Auth
        public function Login($credentials) {
            $username = $credentials->username;
            $password = $credentials->password;
            $auth = new BeeAuth();
            $res = $auth->Login($username, $password);
            echo json_encode($res);
            exit();
        }
        public function GetCaptcha() {
            $builder = new CaptchaBuilder();
            $builder->build();
            $_SESSION["captchaphrase"] = $builder->getPhrase();
            session_write_close();
            echo json_encode(["success" => true, "img" => $builder->inline()]);
        }
        public function SubmitFeedback($feedback) {
            $failed = false;
            if(!isset($_SESSION["captchaphrase"])) { echo "{\"success\": false, \"result\": \"Invalid CAPTCHA. Please refresh the page and try again.\" }"; exit; }
            if(!PhraseBuilder::comparePhrases($_SESSION["captchaphrase"], $feedback->captcha)) { echo "{\"success\": false, \"result\": \"Invalid CAPTCHA.\" }"; exit; }
            session_destroy();
            if(strlen($feedback->feedback) > 1000) { echo "{\"success\": false, \"result\": \"No more than 1000 characters for your feedback, please.\" }"; exit; }
            if(strlen($feedback->name) > 69) { echo "{\"success\": false, \"result\": \"No more than 70 characters for your name, please.\" }"; exit; }
            if(strlen($feedback->contact) > 1000) { echo "{\"success\": false, \"result\": \"No more than 70 characters for your contact info, please.\" }"; exit; }
            if(strlen($feedback->path) > 150) { echo "{\"success\": false, \"result\": \"You shouldn't even see this error!\" }"; exit; }
            $this->sql->ExecuteNonQuery("INSERT INTO feedback (text, name, contact, path, date, issue) VALUES (:t, :n, :c, :p, NOW(), :i)",
                                        ["t" => $feedback->feedback, "n" => $feedback->name, "c" => $feedback->contact, "p" => $feedback->path, "i" => $feedback->issue]);
            echo "{\"success\": true }";
        }

        // Issues
        public function GetAllIssues($company, $showOthers) {
            $relationTypes = $showOthers === "true" ? [1, 2, 3] : [1];
            $relSql = implode(", ", $relationTypes);
            $allIssues = $this->GetFullChain($company, "
            WITH RECURSIVE allentities AS (
                SELECT e.id, e.name, e.name AS namepath
                FROM entity e
                WHERE e.id IN (:keysStr)
                UNION ALL
                SELECT e.id, e.name,
                    CASE r.relationtype
                        WHEN 1 THEN CONCAT(a.namepath, '|', e.name)
                        WHEN 2 THEN CONCAT(a.namepath, '|>', e.name)
                        WHEN 3 THEN CONCAT(a.namepath, '|[', e.name)
                    END
                    AS namepath
                FROM allentities a
                    INNER JOIN relationships r ON r.parent = a.id AND r.relationtype IN ($relSql)
					INNER JOIN entity e ON r.child = e.id
            )
            SELECT a.id AS entityId, a.name AS entityName,
					it.id AS issueTypeId, it.name AS issueType, it.icon AS issueIcon, it.color AS issueColor,
                    i.id, i.issue, i.sourceurl, i.startdate, i.enddate, i.ongoing, i.contentwarning, it.showOnTop, a.namepath
            FROM allentities a
				INNER JOIN issues i ON a.id = i.entity
                INNER JOIN issuetype it ON i.type = it.id
            ORDER BY CASE
                WHEN a.id = :source THEN 
                    CASE
                        WHEN i.ongoing = 1 THEN NOW() + NOW()
                        WHEN i.enddate IS NOT NULL THEN NOW() + i.enddate
                        ELSE NOW() + i.startdate
                    END
				WHEN i.ongoing = 1 THEN NOW()
                WHEN i.enddate IS NOT NULL THEN i.enddate
                ELSE i.startdate
            END DESC", $relationTypes);
            foreach($allIssues as &$val) {
                $val["entityId"] = intval($val["entityId"]);
                $val["issueTypeId"] = intval($val["issueTypeId"]);
                $val["showOnTop"] = intval($val["showOnTop"]) === 1;
            }
            echo json_encode(["success" => true, "result" => $allIssues]);
        }
        public function GetFullIssueList($offset, $types) {
            $pageSize = 15;
            $fullOffset = $offset * $pageSize;
            $typeArr = json_decode($types);
            $whereClause = "";
            $params = [];
            if(count($typeArr) > 0) {
                $res = $this->sql->CreateInClauseFromArray($typeArr);
                $inClause = $res["inClause"];
                $whereClause = "WHERE it.id IN ($inClause)";
                $params = $res["paramsObj"];
            }
            $tbl = $this->sql->GetDataTable("
            SELECT 
                e.id AS entityId, e.name AS entityName,
                it.id AS issueTypeId, it.name AS issueType, it.icon AS issueIcon, it.color AS issueColor,
                i.id, i.issue, i.sourceurl, i.startdate, i.enddate, i.ongoing, i.contentwarning, '' AS namepath
            FROM issues i
                INNER JOIN issuetype it ON i.type = it.id
                INNER JOIN entity e ON i.entity = e.id
            $whereClause
            ORDER BY CASE
                WHEN i.enddate IS NOT NULL THEN i.enddate
                ELSE i.startdate
            END DESC
            LIMIT $pageSize OFFSET $fullOffset", $params);
            echo json_encode(["success" => true, "result" => $tbl]);
        }

        // Issue Types
        public function GetIssueTypes() {
            $tbl = $this->sql->GetDataTable("SELECT id, name, icon, color, showOnTop FROM issuetype ORDER BY name ASC", []);
            echo json_encode(["success" => true, "result" => $tbl]);
        }

        // Categories
        public function GetTopLevelCategories() {
            $tbl = $this->sql->GetDataTable("
            SELECT c.id, c.name, c.icon
            FROM category c
                LEFT JOIN categoryrelationships r ON c.id = r.child
            GROUP BY c.id, c.name, c.icon
            HAVING COUNT(DISTINCT r.parent) = 0
            ORDER BY c.name ASC", []);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function GetChildCategories($category) {
            $tbl = $this->sql->GetDataTable("
            SELECT c.id, c.name, c.icon
            FROM category c
                INNER JOIN categoryrelationships r ON r.child = c.id
            WHERE r.parent = :c
            ORDER BY c.name ASC", ["c" => $category]);
            echo json_encode(["success" => true, "result" => $tbl]);
        }

        // Graph
        public function GetGraphData($company, $showOthers) {
            $relationTypes = $showOthers === "true" ? [1, 2, 3] : [1];
            $relSql = implode(", ", $relationTypes);
            $family = $this->GetFullChain($company, "
			WITH RECURSIVE allentities AS (
                SELECT e.id AS parentId, e.name AS parentName, r.child, r.asOfDate,
                       e2.id AS childId, e2.name AS childName, r.relationtype,
                       e.iconx AS parentx, e.icony AS parenty,
                       e2.iconx AS childx, e2.icony AS childy
                FROM entity e
                    INNER JOIN relationships r ON r.parent = e.id AND r.relationtype IN ($relSql)
                    INNER JOIN entity e2 ON r.child = e2.id
                WHERE e.id IN (:keysStr)
                UNION ALL
                SELECT a.childId AS parentId, a.childName AS parentName, r.child, r.asOfDate,
                       e.id AS childId, e.name AS childName, r.relationtype,
                       a.childx AS parentx, a.childy AS parenty,
                       e.iconx AS childx, e.icony AS childy
                FROM allentities a
                    INNER JOIN relationships r ON r.parent = a.child AND r.relationtype IN ($relSql)
                    INNER JOIN entity e ON r.child = e.id
				WHERE a.child IS NOT NULL
            )
            SELECT parentId, parentName, childId, childName, asOfDate, parentx, parenty, childx, childy, relationtype,  
                CASE WHEN parentId = :source THEN 1 ELSE 0 END AS me
            FROM allentities a", $relationTypes);
            foreach($family as &$val) {
                $val["parentId"] = intval($val["parentId"]);
                $val["childId"] = intval($val["childId"]);
            }
            echo json_encode(["success" => true, "family" => $family, "rows" => $relationTypes]);
        }
        public function GetFullGraphData() {
            $nodes = $this->sql->GetDataTable("SELECT id, name, iconx, icony FROM entity", []);
            $links = $this->sql->GetDataTable("SELECT parent AS source, child AS target, relationtype, asOfDate FROM relationships", []);
            echo json_encode(["success" => true, "nodes" => $nodes, "links" => $links]);
        }
        public function GetFullGraphDataFromCache() {
            echo file_get_contents("./bigData.json");
        }

        // Company
        public function GetCompanyBrowse($offset, $query) {
            $pageSize = 15;
            $fullOffset = $offset * $pageSize;
            $params = [];
            $whereClause = "";
            if($query !== "") {
                $params["q"] = "%$query%";
                $whereClause = "WHERE e.name LIKE :q";
            }
            $tbl = $this->sql->GetDataTable("
            SELECT e.name, c.name AS categoryname, c.icon AS categoryicon, e.id, COUNT(DISTINCT i.id) AS issues, COUNT(DISTINCT r.child) AS children
            FROM entity e
                LEFT JOIN category c ON e.type = c.id
                LEFT JOIN issues i ON i.entity = e.id
                LEFT JOIN relationships r ON r.parent = e.id AND r.relationtype IN (1, 2)
            $whereClause
            GROUP BY e.name, c.name, c.icon, e.id
            ORDER BY e.name ASC
            LIMIT $pageSize OFFSET $fullOffset", $params);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function GetCompanyByCategory($offset, $category) {
            $pageSize = 15;
            $fullOffset = $offset * $pageSize;
            $tbl = $this->sql->GetDataTable("
            WITH RECURSIVE fullcategories AS (
                SELECT c.id, c.name, c.icon
                FROM category c
                WHERE c.id = :c
                UNION ALL
                SELECT c.id, c.name, c.icon
                FROM fullcategories fc
                    INNER JOIN categoryrelationships r ON r.parent = fc.id
                    INNER JOIN category c ON r.child = c.id
            )
            SELECT e.name, fc.name AS categoryname, fc.icon AS categoryicon, e.id, COUNT(DISTINCT i.id) AS issues, COUNT(DISTINCT r.child) AS children
            FROM entity e
                INNER JOIN fullcategories fc ON e.type = fc.id
                LEFT JOIN issues i ON i.entity = e.id
                LEFT JOIN relationships r ON r.parent = e.id AND r.relationtype IN (1, 2)
            GROUP BY e.name, fc.name, fc.icon, e.id
            ORDER BY e.name ASC
            LIMIT $pageSize OFFSET $fullOffset", ["c" => $category]);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function SearchCompanies($query) {
            $tbl = $this->sql->GetDataTable("
            SELECT DISTINCT e.name, e.id
            FROM entity e
                LEFT JOIN synonym s ON e.id = s.entityid
            WHERE e.name LIKE :n
                OR s.synonym LIKE :n
            LIMIT 10", ["n" => "%$query%"]);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function GetAdditionalCompanyInfo($id) {
            $investors = $this->sql->GetStringArray("
            SELECT DISTINCT e.name
            FROM relationships r
                INNER JOIN entity e ON r.parent = e.id
            WHERE r.child = :id AND r.relationtype = 2
            ORDER BY e.name ASC", ["id" => $id], "name");
            $investments = $this->sql->GetStringArray("
            SELECT DISTINCT e.name
            FROM relationships r
                INNER JOIN entity e ON r.child = e.id
            WHERE r.parent = :id AND r.relationtype = 2
            ORDER BY e.name ASC", ["id" => $id], "name");
            $relationships = $this->sql->GetStringArray("
            SELECT DISTINCT e.name
            FROM relationships r
                INNER JOIN entity e ON r.child = e.id OR r.parent = e.id
            WHERE (r.parent = :id OR r.child = :id)
                AND r.relationtype = 3
                AND e.id <> :id
            ORDER BY e.name ASC", ["id" => $id], "name");
            echo json_encode([
                "success" => true, 
                "investors" => $investors, 
                "investments" => $investments, 
                "relationships" => $relationships
            ]);
        }
        public function FindCompany($name) {
            $tbl = $this->sql->GetDataTable("
            SELECT e.name, e.type, e.id, e.description, IFNULL(c.name, '') AS typename, e.iconx, e.icony
            FROM entity e
                LEFT JOIN synonym s ON e.id = s.entityid
                LEFT JOIN category c ON e.type = c.id
            WHERE e.name LIKE :n
                OR s.synonym LIKE :n
            LIMIT 1", ["n" => "$name"]);
            if(count($tbl) !== 1) {
                echo json_encode(["success" => false, "result" => "No results found."]);
                exit;
            }
            $row = $tbl[0];
            $obj = [
                "id" => intval($row["id"]),
                "name" => $row["name"],
                "type" => intval($row["type"]),
                "description" => $row["description"],
                "typename" => $row["typename"],
                "iconx" => $row["iconx"],
                "icony" => $row["icony"]
            ];
            $obj["synonyms"] = $this->sql->GetStringArray("SELECT synonym FROM synonym WHERE entityid = :i", ["i" => $obj["id"]], "synonym");
            
            $parents = $this->sql->GetDataTable("
            WITH RECURSIVE ancestor AS (
                SELECT r.parent AS id, e.name, 0 AS depth, r.parent AS chain
                FROM relationships r
                    INNER JOIN entity e ON r.parent = e.id
                WHERE r.child = :i AND r.relationtype = 1
                UNION ALL
                SELECT ep.id, ep.name, a.depth + 1 AS depth, a.chain
                FROM ancestor a
                    INNER JOIN relationships r ON r.child = a.id AND r.relationtype = 1
                    INNER JOIN entity ep ON r.parent = ep.id
            )
            SELECT *
            FROM ancestor
            ORDER BY depth ASC", ["i" => $obj["id"]]);
            $parentVals = [];
            $obj["parents"] = [];
            $obj["hasAddtlRelationships"] = 0 < $this->sql->GetIntValue("
            WITH RECURSIVE ancestor AS (
                SELECT r.parent AS id, e.name, r.relationtype
                FROM relationships r
                    INNER JOIN entity e ON r.parent = e.id
                WHERE r.child = :i
                UNION ALL
                SELECT ep.id, ep.name, r.relationtype
                FROM ancestor a
                    INNER JOIN relationships r ON r.child = a.id
                    INNER JOIN entity ep ON r.parent = ep.id
            )
            SELECT COUNT(*)
            FROM ancestor
            WHERE relationtype <> 1", ["i" => $obj["id"]]);
            foreach($parents as $k=>$v) {
                $id = intval($v["id"]);
                $depth = intval($v["depth"]);
                if($depth === 0) {
                    $obj["parents"][] = $id;
                    $parentVals[$id] = ["text" => $v["name"], "rootid" => $id, "rootname" => $v["name"]];
                } else {
                    $childid = intval($v["chain"]);
                    $parentVals[$childid]["rootid"] = $id;
                    $parentVals[$childid]["rootname"] = $v["name"];
                }
            }
            $obj["children"] = array_map(function($e) { return ["value" => intval($e["child"]), "text" => $e["name"]]; }, $this->sql->GetDataTable("
            SELECT r.child, e.name
            FROM relationships r
                INNER JOIN entity e ON r.child = e.id
            WHERE r.parent = :i AND r.relationtype = 1", ["i" => $obj["id"]]));
            echo json_encode(["success" => true, "result" => $obj, "parentVals" => $parentVals]);
        }
        private function GetFullChain($company, $query, $relationTypes = [1]) {
            $rels = implode(",", $relationTypes);
            $ancestorCompanies = $this->sql->GetDataTable("
            WITH RECURSIVE ancestor AS (
                SELECT e.id, r2.parent AS toppy
                FROM entity e
                    LEFT JOIN relationships r2 ON r2.child = e.id AND r2.relationtype IN ($rels)
                WHERE e.id = :id
                UNION ALL
                SELECT ep.id, r2.parent AS toppy
                FROM ancestor a
                    INNER JOIN relationships r ON r.child = a.id AND r.relationtype IN ($rels)
                    INNER JOIN entity ep ON r.parent = ep.id
                    LEFT JOIN relationships r2 ON r2.child = ep.id AND r2.relationtype IN ($rels)
            )
            SELECT DISTINCT id
            FROM ancestor
            WHERE toppy IS NULL", ["id" => $company]);
            if(count($ancestorCompanies) === 0) {
                echo json_encode(["success" => false, "result" => "Company not found."]);
                exit;
            }

            $params = ["source" => $company];
            $keysArr = [];
            $maxDepth = 0;
            foreach($ancestorCompanies as $i=>$ac) {
                $key = "root$i";
                $params[$key] = intval($ac["id"]);
                $keysArr[] = ":$key";
            }
            $keysStr = implode(", ", $keysArr);

            return $this->sql->GetDataTable(str_replace(":keysStr", $keysStr, $query), $params);
        }
    }
    $ws = new WebServiceMethods();
    $m = [$ws, $_GET["function"]];
    $callable_name = "";
    if(is_callable($m, false, $callable_name)) {
        $len = strlen("WebServiceMethods::");
        if(substr($callable_name, 0, $len) === "WebServiceMethods::") {
            if($_SERVER["REQUEST_METHOD"] === "POST") {
                $body = file_get_contents('php://input');
                $jsonBody = json_decode($body);
                call_user_func($m, $jsonBody);
            } else {
                $params = [];
                $pos = strpos($_SERVER["QUERY_STRING"], "&");
                if($pos !== false) { $params = explode("/", urldecode(substr(str_replace("+", "%2B", $_SERVER["QUERY_STRING"]), $pos + 1))); }
                call_user_func_array($m, $params);
            }
            return;
        }
    }
	echo "{\"success\": false}";
?>
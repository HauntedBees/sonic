<?php
    session_start();
    require_once(__DIR__."/helpers.php");
    require_once(__DIR__."/beeauth/auth.php");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type,Authorization");
    header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");
	header("Content-Type: application/json");
	if(!isset($_GET["function"])) { echo "{\"success\": false}"; exit; }
	class WebServiceMethods {
		private $sql;
		public function __construct() { $this->sql = new SQLHelper(); }
		public function Fail() { echo "{\"success\": false}"; exit; }
		public function Auth() { echo json_encode(["success" => true]); }

        // Issues
        public function GetIssues($parent) {
            $tbl = $this->sql->GetDataTable("
            SELECT i.id, i.issue, i.type, i.sourceurl, i.contentwarning, i.startdate, i.enddate, i.ongoing
            FROM issues i
                INNER JOIN issuetype it ON i.type = it.id
            WHERE i.entity = :id
            ORDER BY CASE
                WHEN i.ongoing = 1 THEN NOW() + i.startdate
                WHEN i.enddate IS NOT NULL THEN i.enddate
                ELSE i.startdate
            END ASC", ["id" => $parent]);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function SaveIssue($i) {
            if($i->id === 0) {
                $i->id = $this->sql->InsertAndReturnID("INSERT INTO issues (entity, type, issue, sourceurl, startdate, enddate, contentwarning, ongoing) VALUES (:e, :t, :d, :s, :d1, :d2, :cw, :o)", 
                    ["e" => $i->companyid, "t" => $i->type, "d" => $i->issue, "s"=> $i->sourceurl, "d1" => $i->startdate, "d2" => $i->enddate, "cw" => $i->contentwarning, "o" => $i->ongoing ? 1 : 0]);
            } else {
                $this->sql->ExecuteNonQuery("UPDATE issues SET type = :t, issue = :d, sourceurl = :s, startdate = :d1, enddate = :d2, contentwarning = :cw, ongoing = :o WHERE id = :id", 
                    ["t" => $i->type, "d" => $i->issue, "s"=> $i->sourceurl, "d1" => $i->startdate, "d2" => $i->enddate, "cw" => $i->contentwarning, "o" => $i->ongoing ? 1 : 0, "id" => $i->id]);
            }
            echo json_encode(["success" => true, "result" => $i->id]);
        }
        public function DeleteIssue($id) {
            $this->sql->ExecuteNonQuery("DELETE FROM issues WHERE id = :id", ["id" => $id]);
            echo json_encode(["success" => true]);
        }
        
        // Feedback
        public function GetFeedback() {
            $tbl = $this->sql->GetDataTable("
            SELECT f.id, f.path, f.text, f.name, f.contact, f.date, i.id AS issue, e.name AS issueParent
            FROM feedback f
				LEFT JOIN issues i ON f.issue = i.id
                LEFT JOIN entity e ON i.entity = e.id
            WHERE f.dismissed = 0
            ORDER BY date DESC", []);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function DismissFeedback($id) {
            $this->sql->ExecuteNonQuery("UPDATE feedback SET dismissed = 1 WHERE id = :id", ["id" => $id]);
            echo json_encode(["success" => true]);
        }

        // Issue Types
        public function SaveIssueType($it) {
            if($it->id === 0) {
                $it->id = $this->sql->InsertAndReturnID("INSERT INTO issuetype (name, icon, color) VALUES (:n, :i, :c)", ["n" => $it->name, "i" => $it->icon, "c" => $it->color]);
            } else {
                $this->sql->ExecuteNonQuery("UPDATE issuetype SET name = :n, icon = :i, color = :c WHERE id = :id", ["n" => $it->name, "i" => $it->icon, "c" => $it->color, "id" => $it->id]);
            }
            echo json_encode(["success" => true, "result" => $it->id]);
        }

        // Categories
        public function GetCategories() {
            $tbl = $this->sql->GetDataTable("
            SELECT c.id, c.icon, c.name, COUNT(DISTINCT e.id) count
            FROM category c
				LEFT JOIN entity e ON c.id = e.type
			GROUP BY c.id, c.icon, c.name
            ORDER BY c.name ASC", []);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function GetCategoryParents($catID) {
            $tbl = $this->sql->GetStringArray("
            SELECT parent
            FROM categoryrelationships
            WHERE child = :id", ["id" => $catID], "parent");
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function SearchCategories($query) {
            $tbl = $this->sql->GetDataTable("
            SELECT id, name
            FROM category
            WHERE name LIKE :n
            LIMIT 10", ["n" => "%$query%"]);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function SaveCategory($it) {
            try {
                $this->sql->BeginTransaction();
                if($it->id === 0) {
                    $it->id = $this->sql->InsertAndReturnID("INSERT INTO category (icon, name) VALUES (:i, :n)", ["n" => $it->name, "i" => $it->icon]);
                } else {
                    $this->sql->ExecuteNonQuery("UPDATE category SET name = :n, icon = :i WHERE id = :id", ["n" => $it->name, "i" => $it->icon, "id" => $it->id]);
                    $this->sql->ExecuteNonQuery("DELETE FROM categoryrelationships WHERE child = :id", ["id" => $it->id]);
                }
                $this->sql->DoMultipleInsert($it->id, $it->parents, "INSERT INTO categoryrelationships (child, parent) VALUES ");
                $this->sql->CommitTransaction();
                echo json_encode(["success" => true, "result" => $it->id]);
            } catch(Exception $e) {
                $this->sql->RollbackTransaction();
                echo json_encode(["success" => false, "result" => $e]);
            }
        }

        // Company
        public function GetCompanies() {
            $tbl = $this->sql->GetDataTable("
            SELECT e.name, c.name AS categoryname, e.id, COUNT(DISTINCT i.id) AS issues, COUNT(DISTINCT r.child) AS children
            FROM entity e
                LEFT JOIN category c ON e.type = c.id
                LEFT JOIN issues i ON i.entity = e.id
                LEFT JOIN relationships r ON r.parent = e.id
            GROUP BY e.name, c.name, e.id
            ORDER BY e.name ASC", []);
            echo json_encode(["success" => true, "result" => $tbl]);
        }
        public function SaveCompany($company) {
            try {
                $this->sql->BeginTransaction();
                if($company->newtype !== "") {
                    $company->type = $this->sql->InsertAndReturnID("INSERT INTO category (name) VALUES (:c)", ["c" => $company->newtype]);
                }
                if($company->id === 0) {
                    $existingCount = $this->sql->GetIntValue("SELECT COUNT(*) FROM entity WHERE name = :n", ["n" => $company->name]);
                    if($existingCount > 0) {
                        echo json_encode(["success" => false, "result" => "A record with this name already exists."]);
                        return;
                    }
                    $company->id = $this->sql->InsertAndReturnID("
                    INSERT INTO entity (name, type, description, iconx, icony)
                        VALUES (:n, :t, :d, :x, :y)",
                    ["n" => $company->name, "t" => $company->type, "d" => $company->description, "x" => $company->iconx, "y" => $company->icony]);
                } else {
                    $this->sql->ExecuteNonQuery("
                    UPDATE entity SET 
                        name = :n, type = :t, description = :d, iconx = :x, icony = :y
                    WHERE id = :i",
                    ["n" => $company->name, "t" => $company->type, "d" => $company->description, "x" => $company->iconx, "y" => $company->icony, "i" => $company->id]);
                    $this->sql->ExecuteNonQuery("DELETE FROM synonym WHERE entityid = :i", ["i" => $company->id]);
                    $this->sql->ExecuteNonQuery("DELETE FROM relationships WHERE child = :i", ["i" => $company->id]);
                }
                $this->sql->DoMultipleInsert($company->id, $company->synonyms, "INSERT INTO synonym (entityid, synonym) VALUES ");
                $this->sql->DoMultipleInsert($company->id, $company->parents, "INSERT INTO relationships (child, parent) VALUES ");
                $this->sql->CommitTransaction();
                echo json_encode(["success" => true, "result" => $company->id]);
            } catch(Exception $e) {
                $this->sql->RollbackTransaction();
                echo json_encode(["success" => false, "result" => $e]);
            }
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        http_response_code(200);
        exit;
    }

    $bee = new BeeAuth();
    $bee->ValidateSession();

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
                if($pos !== false) { $params = explode("/", urldecode(substr($_SERVER["QUERY_STRING"], $pos + 1))); }
                call_user_func_array($m, $params);
            }
            return;
        }
    }
	echo "{\"success\": false}";
?>
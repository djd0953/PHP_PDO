<?php
    require_once($_SERVER["DOCUMENT_ROOT"]."/DBConnector.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/vo/vo.php");
    
    class DAO_T
    {
        const STDLIMIT = 1000;
        
        public $conn;
        public $sql = "";
        public $table = "";
        public $selectKey = "";
        public $voName = "";
        
        //////////////////////////////////////////////////////////////////////////
        //////////                DB 기본 구동 로직                      //////////
        //////////////////////////////////////////////////////////////////////////
        function __construct(string $table, string $selectKey, string $voName = null)
        {
            
            $dbconn = new DBCONNECT;
            $this->conn = $dbconn->connect();

            $this->table = $table;
            $this->selectKey = $selectKey;

            if ($voName === null) $this->voName = "{$this->table}_vo";
            else $this->voName = $voName;
        }

        function Execute() : void
        {
            try
            {
                $statement = $this->conn->query($this->sql);
                if (!$statement) throw new PDOException("SQL 문구에 오류가 있습니다.");
            }
            catch (PDOException $e)
            {
                writeLog("EXECUTE() :: {$e->getMessage()} ({$this->sql})");
            }
            catch (Exception $e)
            {
                writeLog("EXECUTE() :: {$e->getMessage()} ({$this->sql})");
            }
        }

        function Query() : array
        {
            try
            {
                $statement = $this->conn->query($this->sql);
                $statement->setFetchMode(PDO::FETCH_CLASS, "{$this->voName}");
                $rtv = $statement->FetchAll();
                if (!$rtv) throw new PDOException("SQL 문구에 오류가 있습니다.");
            }
            catch (PDOException $e)
            {
                writeLog("QUERY() :: {$e->getMessage()} ({$this->sql})");
                $rtv = array();
            }
            catch (Exception $e)
            {
                writeLog("QUERY() :: {$e->getMessage()} ({$this->sql})");
                $rtv = array();
            }

            return $rtv;
        }

        function Prepare(object $vo) : array
        {
            try
            {
                $stmt = $this->conn->prepare($this->sql);
                foreach( $vo as $k => &$v )
                {
                    switch(gettype($v))
                    {
                        case "double":
                        case "string":
                            $stmt->bindParam($k, $v, PDO::PARAM_STR);
                            break;
                            
                        case "integer":
                            $stmt->bindParam($k, $v, PDO::PARAM_INT);
                            break;
    
                        case "array":
                            for($i = 0; $i < count($v); $i++)
                            {
                                $stmt->bindValue($i + 1, $v[$i]);
                            }
                            break;

                        default:
                            throw new Exception("값이 잘못되었습니다.(".gettype($v).")");
                    }
                }
    
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_CLASS, "{$this->voName}");
                $vo = $stmt->fetchAll();
    
                return $vo;
            }
            catch (Exception $e)
            {
                writeLog("PRAPARE() :: {$e->getMessage()}({$this->sql})");
            }
            catch (PDOException $e)
            {
                writeLog("PRAPARE() :: {$e->getMessage()}({$this->sql})");
            }
        }

        function InsertId()
        {
            return $this->conn->lastInsertId();
        }

        //////////////////////////////////////////////////////////////////////////
        //////////                  기본 DML  로직                       //////////
        //////////////////////////////////////////////////////////////////////////
        public function SQL(string $sql) : array
        {
            $this->sql = $sql;

            if (strpos($sql, "SELECT") === 0) return $this->Query();
            else $this->Execute();
        }

        public function SQLToArray(string $sql) : array // VO Class를 갖지 않는 SQL문
        {
            $this->sql = $sql;

            try
            {
                $statement = $this->conn->query($this->sql);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $rtv = $statement->FetchAll();

                if (!$rtv) throw new Exception("SQL 문구에 오류가 있습니다.");
            }
            catch (Exception $e)
            {
                $rtv = array();
            }

            return $rtv;
        }

        public function Select(string $where = "1=1", string $order = "", int $limit = self::STDLIMIT, int $count = 0) : array
        {
            try
            {
                $this->sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY ";
                $this->sql .= $order == "" ? "{$this->selectKey}" : "{$order}";
    
                if ($limit >= 0) $this->sql .= " LIMIT {$limit}";
                if ($count > 0) $this->sql .= ",{$count}";
    
                $rtv = $this->Query();
            }
            catch (Exception $e)
            {
                $rtv = Array();
            }

            return $vo;
        }

        public function Select_pre(object $obj, string $where = "1=1", string $order = "", int $limit = self::STDLIMIT, int $count = 0) : array
        {   
            try
            {
                $this->sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY ";
                $this->sql .= $order == "" ? "{$this->selectKey}" : "{$order}";
    
                if ($limit >= 0) $this->sql .= " LIMIT {$limit}";
                if ($count > 0) $this->sql .= ",{$count}";
    
                $rtv = $this->Prepare($obj);
            }
            catch (Exception $e)
            {
                $rtv = array();
            }

            return $rtv;
        }

        public function Single(string $where = "1=1", string $order = "", int $limit = self::STDLIMIT, int $count = 0)
        {
            // $vo->Col 식으로 사용
            $rtv = false;

            try
            {
                $voArray = $this->Select($where, $order, $limit, $count);
    
                foreach ($voArray as $l)
                {
                    $rtv = $l;
                    break;
                }
            }
            catch (Exception $e) { }

            return $rtv;
        }

        public function ArrayToSingle(string $sql)
        {
            // $vo["Col"] 식으로 사용
            $rtv = false;

            try
            {
                $voArray = $this->SQLToArray($sql);
    
                foreach ($voArray as $l)
                {
                    $rtv = $l;
                    break;
                }
            }
            catch (Exception $e) { }

            return $rtv;
        }

        public function Insert($vo) : void
        {
            // $vo의 PK가 하나이고 Auto_Increment로 입력하지 않아도 될 경우 $vo의 첫번째 변수를 null로 입력
            try
            {
                $select = "";
                $value = "";
    
                foreach ($vo as $key => $val)
                {
                    if ($select !== "") $select .= ", ";
                    if ($value !== "") $value .= ", " ;
    
                    if (key($vo) === $key && $val === null) { }
                    else
                    {
                        $select .= "{$key}";
        
                        if ($val === null) $value .= "NULL";
                        else if (gettype($val) == "integer" || gettype($val) == "double") $value .= "{$val}" ;
                        else if (gettype($val) == "string") $value .= "'{$val}'";
                        else throw new Exception("INSERT VALUE 값이 잘못되었습니다. (".print_r($vo).")");
                    }
                }

                $this->sql = "INSERT INTO {$this->table}( {$select} ) VALUES ( {$value} )";
                $this->Execute();
            }
            catch (Exception $e)
            {
                writeLog("INSERT() :: {$e->getMessage()} ({$this->sql})");
            }
        }

        public function Update($value, $where) : void
        {
            try
            {
                if ($value == "" || $where == "")
                {
                    throw new Exception("Value 및 Where 조건이 비어있습니다.");
                }
    
                $this->sql = "UPDATE {$this->table} SET {$value} WHERE {$where}";
                $this->Execute();
            }
            catch(Exception $e)
            {
                writeLog("UPDATE() :: {$e->getMessage()} ({$this->sql})");
            }
        }

        public function Delete($where) : void
        {
            try
            {
                if( $where == "" )
                {
                    throw new Exception("Delete Where절 조건이 설정되지 않았습니다 (".print_r($where).")");
                }
    
                $this->sql = "DELETE FROM {$this->table} WHERE {$where}";
                $this->Execute();
            }
            catch(Exception $e)
            {
                writeLog("DELETE() :: {$e->getMessage()} ({$this->sql})");
            }
        }

        public function RowCount(string $where = "1=1")
        {
            $rtv = 0;

            try
            {
                $this->sql = "SELECT count(*) AS cnt FROM {$this->table} WHERE {$where}";

                $statement = $this->conn->query($this->sql);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $vo = $statement->FetchAll();

                if( !$vo ) throw new Exception("SQL 문구에 오류가 있습니다");

                $rtv = (int) $vo[0]["cnt"];
            }
            catch(Exception $e)
            {
                writeLog("RowCount() :: {$e->getMessage()} ({$this->sql})");
            }

            return $rtv;
        }

        public function ExistTable($table = null)
        {
            $rtv = false;

            try
            {
                $table = $table !== null ? $table : $this->table;
                $statement = $this->conn->query("SHOW TABLES LIKE '{$table}'");
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $vo = $statement->FetchAll();

                if (count($vo) > 0) $rtv = true;
                else $rtv = false;
            }
            catch(PDOException $e)
            {
                writeLog("ExistTable() :: {$e->getMessage()} ({$this->sql})");
            }

            return $rtv;
        }
    }
?>

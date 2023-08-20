<?php
	class DBCONNECT
    {
        private $host = 'localhost';
        private $port = '3306';
        private $dbname = 'dbName';
        private $charset = 'utf8';
        private $username = 'user';
        private $password = 'password';

        public $db_conn;
    
        function connect()
        {
            $this->db_conn = new PDO("mysql:host={$this->host}:{$this->port};dbname={$this->dbname};charset={$this->charset}", "{$this->username}", "{$this->password}");
            $this->db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->db_conn;
        }
    }
?>

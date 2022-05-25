<?php
    class MySQL_Connecter{


        //테스트
        private $host = "192.168.0.14";
        private $port = "3306";
        private $db_name = "shop";
        private $username = "gameuser";
        private $password = "Dlswmd!@12";
        private $conn;


        // connect database using PDO
        public function ConnectServer(){
            try{

                $this->conn = new PDO("mysql:host=".$this->host.";port=".$this->port.";dbname=".$this->db_name.";charset=utf8", $this->username , $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->conn;
            }
            catch(PDOException $e){
                echo "Connection Error -->> ",$e->getMessage();
                echo "<br>Error Code -->> ",$e->getCode();
                echo "<br>Error occur in File -->> ",$e->getFile();
                echo "<br>Error occur on Line no -->> ",$e->getLine();

                $this->conn = null; // close connection in PDO
            }
        }
    }

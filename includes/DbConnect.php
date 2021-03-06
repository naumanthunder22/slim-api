<?php

class DbConnect
{
    // specify your own database credentials
    private $host;
    private $db_name;
    private $username;
    private $password;


    //Variable to store database link
    public $conn;

    //Class constructor
    function __construct()
    {
        //Including the constants.php file to get the database constants
        require_once dirname(__FILE__) . '/Constants.php';

        $this->host 		=   DB_HOST;
        $this->db_name      =   DB_NAME;
        $this->username     =   DB_USERNAME;
        $this->password     =   DB_PASSWORD;

    }

    //This method will connect to the database
    function connect()
    {
        //Including the constants.php file to get the database constants
        require_once dirname(__FILE__) . '/Constants.php';

        //connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        //Checking if any error occured while connecting
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            return null;
        }

        //finally returning the connection link
        return $this->conn;
    }

    // get the database connection
    public function getConnection(){

        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }


}
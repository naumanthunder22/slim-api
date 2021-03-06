<?php

class child_user
{
    // database connection and table name
    public $conn;
    public $table_name = "child";

    // object properties
    public $child_id;
    public $permission_status;
    public $parent_id;
    public $child_name;
    public $child_device;



    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;

    }

    // create linked_user
    function create()
    {

        //if(!$this->isUserExist()){

        // query to insert record
        $query = "INSERT INTO 
                " . $this->table_name . "
            SET
                child_id	                =:child_id,
                permission_status           =:permission_status,
                parent_id 	                =:parent_id,
                child_name  		        =:child_name,                           
                child_device  		        =:child_device                             
                ";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->child_id = htmlspecialchars(strip_tags($this->child_id));
        $this->permission_status = htmlspecialchars(strip_tags($this->permission_status));
        $this->parent_id = htmlspecialchars(strip_tags($this->parent_id));
        $this->child_name = htmlspecialchars(strip_tags($this->child_name));
        $this->child_device = htmlspecialchars(strip_tags($this->child_device));


        // bind values
        $stmt->bindValue(":child_id", null, PDO::PARAM_INT);
        $stmt->bindParam(":permission_status", $this->permission_status);
        $stmt->bindParam(":parent_id", $this->parent_id);
        $stmt->bindParam(":child_name", $this->child_name);
        $stmt->bindParam(":child_device", $this->child_device);


        // execute query
        if ($stmt->execute()) {
            return CHILD_USER_CREATED;
        } else {
            return CHILD_USER_FAILURE;
        }
        // }
        //  return USER_EXISTS;

    }

}
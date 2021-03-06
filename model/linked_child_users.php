<?php


class linked_child_users
{
    // database connection and table name
    public $conn;
    public $table_name = "linked_child_users";

    // object properties
    public $linked_child_users_id;
    public $child_id;
    public $parent_id;
    public $token_id;


    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;

    }

    // create linked_user
    function create(){

        //if(!$this->isUserExist()){

        // query to insert record
        $query = "INSERT INTO 
                " . $this->table_name . "
            SET
                linked_child_users_id	=:linked_child_users_id,
                child_id                =:child_id,
                parent_id 	            =:parent_id,
                token_id  		        =:token_id                             
                ";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->linked_child_users_id=htmlspecialchars(strip_tags($this->linked_child_users_id));
        $this->child_id=htmlspecialchars(strip_tags($this->child_id));
        $this->parent_id=htmlspecialchars(strip_tags($this->parent_id));
        $this->token_id=htmlspecialchars(strip_tags($this->token_id));


        // bind values
        $stmt->bindValue(":linked_child_users_id", null, PDO::PARAM_INT);
        $stmt->bindParam(":child_id", $this->child_id);
        $stmt->bindParam(":parent_id", $this->parent_id);
        $stmt->bindParam(":token_id", $this->token_id);


        // execute query
        if($stmt->execute()){
            return LINKED_CHILD_USER_CREATED;
        }
        else{
            return LINKED_CHILD_USER_FAILURE;
        }
        // }
        //  return USER_EXISTS;

    }

}
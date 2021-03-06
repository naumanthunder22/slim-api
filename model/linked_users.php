<?php


class linked_users
{

    // database connection and table name
    public $conn;
    public $table_name = "linked_users";

    // object properties
    public $account_id;
    public $linked_users_id;
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
                linked_users_id	=:linked_users_id,
                account_id 	=:account_id,
                token_id  		=:token_id
                             
                ";

            // prepare query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->linked_users_id=htmlspecialchars(strip_tags($this->linked_users_id));
            $this->account_id=htmlspecialchars(strip_tags($this->account_id));
            $this->token_id=htmlspecialchars(strip_tags($this->token_id));


            // bind values
            $stmt->bindValue(":linked_users_id", null, PDO::PARAM_INT);
            $stmt->bindParam(":account_id", $this->account_id);
            $stmt->bindParam(":token_id", $this->token_id);


            // execute query
            if($stmt->execute()){
                return LINKED_USER_CREATED;
            }
            else{
                return LINKED_USER_FAILURE;
            }
       // }
      //  return USER_EXISTS;

    }

}
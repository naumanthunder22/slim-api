<?php


class account
{

    // database connection and table name
    public $conn;
    public $table_name = "account";

    // object properties
    public $account_id;
    public $user_name;
    public $email;
    public $password;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read all accounts
    function readAllRecords(){

        // select all query
        $query = "SELECT  * From ".$this->table_name."";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }

    // create account
    function create(){

        if(!$this->isUserExist()){

            // query to insert record
            $query = "INSERT INTO 
                " . $this->table_name . "
            SET
                account_id	=:account_id,
                user_name 	=:user_name,
                email  		=:email,
                password   	=:password                
                ";

            // prepare query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->account_id=htmlspecialchars(strip_tags($this->account_id));
            $this->user_name=htmlspecialchars(strip_tags($this->user_name));
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->password=htmlspecialchars(strip_tags($this->password));

            // bind values
            $stmt->bindValue(":account_id", null, PDO::PARAM_INT);
            $stmt->bindParam(":user_name", $this->user_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);

            // execute query
            if($stmt->execute()){
                return USER_CREATED;
            }
            else{
                return USER_FAILURE;
            }
        }
        return USER_EXISTS;

    }

    // read account by account_id
    function readOneRecord(){

        // query to read single record
        $query = "SELECT
                *
            FROM
                " . $this->table_name . " 
            WHERE
                account_id = ?
            LIMIT
                0,1";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of account to read
        $stmt->bindParam(1, $this->account_id);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount()>0)
        {
            // set values to object properties
            $this->account_id		= $row['account_id'];
            $this->user_name		= $row['user_name'];
            $this->email			= $row['email'];
            $this->password			= $row['password'];
        }

        return $stmt;

    }

    // update the account by account_id
    function updateAccount($user_name, $email, $password){

        // Extra validation is needed to check if an ID exists in the database
        $stmt = $this->readOneRecord();
        $num = $stmt->rowCount();

        if($num > 0){

            // update query
            $query = "UPDATE
                " . $this->table_name . "
            SET
                user_name 	=:user_name,
                email  		=:email,
                password   	=:password 
            WHERE
                account_id = :account_id";

            // prepare query statement
            $stmt = $this->conn->prepare($query);

            $this->user_name		= $user_name;
            $this->email			= $email;
            $this->password			= $password;

            // sanitize
            $this->user_name=htmlspecialchars(strip_tags($this->user_name));
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->password=htmlspecialchars(strip_tags($this->password));

            // bind new values
            $stmt->bindParam(":user_name", $this->user_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindValue(":account_id", $this->account_id);

            // execute the query
            if($stmt->execute()){
                return USER_UPDATED;
            }
            else{
                return USER_NOT_UPDATED;
            }
        }
        return USER_NOT_FOUND;
    }

    // delete the account
    function deleteAccount(){

        // Extra validation is needed to check if an ID exists in the database
        $stmt = $this->readOneRecord();
        $num = $stmt->rowCount();

        if($num > 0){

            // delete query
            $query = "DELETE FROM " . $this->table_name . " WHERE account_id = ?";

            // prepare query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->account_id=htmlspecialchars(strip_tags($this->account_id));

            // bind id of record to delete
            $stmt->bindParam(1, $this->account_id);

            // execute query
            if($stmt->execute()){
                return USER_DELETED;
            }
            else{
                return USER_NOT_DELETED;
            }
        }

        return USER_NOT_FOUND;

    }

    private function isUserExist(){

        // query to read single record
        $query = "SELECT
                *
            FROM
                " . $this->table_name . " 
            WHERE
                email = ? OR user_name = ?
            LIMIT
                0,1";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of account to read
        $stmt->bindParam(1, $this->email);
        $stmt->bindParam(2, $this->user_name);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount()>0)
        {
            // set values to object properties
            $this->account_id		= $row['account_id'];
            $this->user_name		= $row['user_name'];
            $this->email			= $row['email'];
            $this->password			= $row['password'];
        }


        return $stmt->rowCount() > 0;
    }

    function userLogin($email, $password)
    {
        $this->email        = $email;
        $this->user_name    = $email;

        if($this->isUserExist()){

            //finally signing in but have to check user role later
            if($this->password===$password /*&& $role === $user_role_from_db*/)
            {
                return USER_AUTHENTICATED;
            }
            else
            {
                return USER_PASSWORD_DO_NOT_MATCH;
            }
        }
        return USER_NOT_FOUND;
    }




    
}
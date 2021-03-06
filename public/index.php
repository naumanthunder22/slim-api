<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../vendor/autoload.php';
// include database and object files
require_once '../includes/DbConnect.php';
require_once '../model/account.php';
require_once '../model/linked_users.php';
require_once '../model/linked_child_users.php';
require_once '../model/child_user.php';
require_once '../model/firebase.php';
require_once '../model/push.php';

//Including the constants.php file to get the database constants
require_once '../includes/Constants.php';


//GET	    To Retrieve Values from Database
//POST	    To Insert new Values to Database
//PUT	    To Update Existing Values in the Database
//DELETE	To Delete Values from Database


$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);


$app->get('/getallaccounts', function(Request $request, Response $response){

    // instantiate database and account object
    $database = new DbConnect();
    $db = $database->getConnection();

    // initialize object
    $account = new account($db);

    // read account will be here
    // query accounts
    $stmt = $account->readAllRecords();
    $num = $stmt->rowCount();

    // check if more than 0 record found
    if($num>0){

        // response array
        $response_data=array();
        $response_data['record_count']= $num;
        $response_data['user_records']=array();

        // retrieve our table contents
        // fetch() is faster than fetchAll()
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $account_item=array(
                "account_id"	=> $row['account_id'],
                "user_name"		=> $row['user_name'],
                "email"			=> $row['email'],
                "password"		=> $row['password']
            );

            array_push($response_data['user_records'], $account_item);
        }

        $response_data['error'] = false;

        // write accounts data in json format as response
        $response->write(json_encode($response_data));

        return $response
            ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('Content-type', 'application/json')
            ->withStatus(OK);
    }

    else
    {
        $response_data['error'] = true;

        // tell the user no accounts found
        $response_data['message'] = 'No Account Found';

        return $response
            ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('Content-type', 'application/json')
            ->withStatus(NOT_FOUND);
    }
});

$app->get('/getaccount/{account_id}', function(Request $request, Response $response, array $args){

    $id = $args['account_id'];
    if(!empty($id) && $id!=null)
    {
        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $account = new account($db);

        // set ID property of record to read
        $account->account_id = $id;

        // read the details of account by id
        $stmt = $account->readOneRecord();
        $num = $stmt->rowCount();

        if($num > 0){

            // create array
            $user_record = array(
                "account_id"	=> $account->account_id,
                "user_name"		=> $account->user_name,
                "email"			=> $account->email,
                "password"		=> $account->password
            );

            $response_data = array();
            $response_data['error'] = false;
            $response_data['user_record'] = $user_record;
            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(OK);
        }
        else{

            $response_data['error'] = true;
            // tell the user account not found
            $response_data['message'] = 'Account Not Found';
            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(NOT_FOUND);
        }
    }
    else
    {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Unable to delete Account. ID is Missing.';
        $response->write(json_encode($error_detail));
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);
});

$app->post('/createaccount', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('user_name', 'email', 'password'), $request, $response)){

        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $account = new account($db);

        // set Account property values
        $account->user_name = $request_data['user_name'];
        $account->email     = $request_data['email'];
        $account->password  = $request_data['password'];

        // create the account
        $result = $account->create();

        if($result == USER_CREATED){

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Account created successfully';
            $message['new_account_id'] = $account->conn->lastInsertId();

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(CREATED);

        }
        else if($result == USER_FAILURE){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
        else if($result == USER_EXISTS){

            $message = array();
            $message['error'] = true;
            $message['error_type'] = USER_EXISTS;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(CONFLICT);
        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


});

$app->put('/updateaccount/{account_id}', function(Request $request, Response $response, array $args){

    $id = $args['account_id'];

    if(!haveEmptyParameters(array('user_name', 'email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $account = new account($db);

        // set ID property of record to read
        $account->account_id = $id;

        // set Account property values
        $user_name = $request_data['user_name'];
        $email     = $request_data['email'];
        $password  = $request_data['password'];

        // update the details of account by id
        $result = $account->updateAccount($user_name, $email, $password);

        if($result == USER_UPDATED){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Account has been Updated';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(OK);
        }
        else if($result == USER_NOT_UPDATED){

            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_NOT_UPDATED;
            $response_data['message'] = 'Unable to update account. Plase try again later';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
        else if($result == USER_NOT_FOUND){

            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_NOT_FOUND;
            $response_data['message'] = 'User Not Found';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(NOT_FOUND);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


    });

$app->delete('/deleteaccount/{account_id}', function(Request $request, Response $response, array $args){

    $id = $args['account_id'];
    if(!empty($id) && $id!=null)
    {
        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $account = new account($db);

        // set account id to be deleted
        $account->account_id = $id;

        // delete the account
        $result = $account->deleteAccount();

        if($result == USER_DELETED){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Account has been deleted';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(OK);

        }
        // if unable to delete the account
        else if($result == USER_NOT_DELETED){
            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_NOT_DELETED;
            $response_data['message'] = 'Unable to delete account. Plase try again later';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
        else if($result == USER_NOT_FOUND){

            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_NOT_FOUND;
            $response_data['message'] = 'User Not Found';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(NOT_FOUND);
        }
    }
    else
    {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Unable to delete Account. ID is Missing.';
        $response->write(json_encode($error_detail));
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);
});

$app->post('/userlogin', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $account = new account($db);

        $email     = $request_data['email'];
        $password  = $request_data['password'];

        $result = $account->userLogin($email, $password);

        if($result == USER_AUTHENTICATED){
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['user']      = array(
                "account_id"    => $account->account_id,
                "user_name"     => $account->user_name,
                "email"         => $account->email,
                "password"      => $account->password
            );

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(OK);
        }
        else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_PASSWORD_DO_NOT_MATCH;
            $response_data['message'] = 'Invalid credential';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(UNAUTHORIZED);
        }
        else if($result == USER_NOT_FOUND){

            $response_data = array();
            $response_data['error'] = true;
            $response_data['error_type'] = USER_NOT_FOUND;
            $response_data['message'] = 'User Not Found';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(NOT_FOUND);
        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


});

$app->put('/updatepassword', function(Request $request, Response $response){

});


$app->post('/createlinkeduser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('account_id', 'token_id'), $request, $response)){

        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $linked_users = new linked_users($db);


        $linked_users->account_id = $request_data['account_id'];
        $linked_users->token_id     = $request_data['token_id'];



        $result = $linked_users->create();

        if($result == LINKED_USER_CREATED){

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Linked successfully';
            $message['new_account_id'] = $linked_users->conn->lastInsertId();


            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(CREATED);

        }
        else if($result == LINKED_USER_FAILURE){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
//        else if($result == USER_EXISTS){
//
//            $message = array();
//            $message['error'] = true;
//            $message['error_type'] = USER_EXISTS;
//            $message['message'] = 'User Already Exists';
//
//            $response->write(json_encode($message));
//
//            return $response
//                ->withHeader('Access-Control-Allow-Origin','*')
//                ->withHeader('Access-Control-Allow-Headers','access')
//                ->withHeader('Access-Control-Allow-Methods','POST')
//                ->withHeader('Access-Control-Allow-Credentials','true')
//                ->withHeader('Content-type', 'application/json')
//                ->withStatus(CONFLICT);
//        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


});


$app->post('/createlinkedchilduser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('child_id', 'parent_id', 'token_id'), $request, $response)){

        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $linked_child_users = new linked_child_users($db);

        $linked_child_users->child_id               = $request_data['child_id'];
        $linked_child_users->parent_id 	            = $request_data['parent_id'];
        $linked_child_users->token_id  		        = $request_data['token_id'];

        // create the account
        $result = $linked_child_users->create();

        if($result == LINKED_CHILD_USER_CREATED){

            $notificationBody = "Your Child is Linked Successfully";
            SendFCMNotification($request,$response,$notificationBody);

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Child Linked successfully';
            $message['new_account_id'] = $linked_child_users->conn->lastInsertId();

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(CREATED);

        }
        else if($result == LINKED_CHILD_USER_FAILURE){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
//        else if($result == USER_EXISTS){
//
//            $message = array();
//            $message['error'] = true;
//            $message['error_type'] = USER_EXISTS;
//            $message['message'] = 'User Already Exists';
//
//            $response->write(json_encode($message));
//
//            return $response
//                ->withHeader('Access-Control-Allow-Origin','*')
//                ->withHeader('Access-Control-Allow-Headers','access')
//                ->withHeader('Access-Control-Allow-Methods','POST')
//                ->withHeader('Access-Control-Allow-Credentials','true')
//                ->withHeader('Content-type', 'application/json')
//                ->withStatus(CONFLICT);
//        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


});

$app->post('/createchilduser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('permission_status', 'parent_id', 'child_name', 'child_device'), $request, $response)){

        $request_data = $request->getParsedBody();

        // instantiate database and account object
        $database = new DbConnect();
        $db = $database->getConnection();

        // initialize object
        $child_users = new child_user($db);

        $child_users->permission_status         = $request_data['permission_status'];
        $child_users->parent_id 	            = $request_data['parent_id'];
        $child_users->child_name  		        = $request_data['child_name'];
        $child_users->child_device  		    = $request_data['child_device'];

        // create the account
        $result = $child_users->create();

        if($result == CHILD_USER_CREATED){

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Child Created successfully';
            $message['new_account_id'] = $child_users->conn->lastInsertId();

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(CREATED);

        }
        else if($result == CHILD_USER_FAILURE){

            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Access-Control-Allow-Origin','*')
                ->withHeader('Access-Control-Allow-Headers','access')
                ->withHeader('Access-Control-Allow-Methods','POST')
                ->withHeader('Access-Control-Allow-Credentials','true')
                ->withHeader('Content-type', 'application/json')
                ->withStatus(SERVICE_UNAVAILABLE);
        }
//        else if($result == USER_EXISTS){
//
//            $message = array();
//            $message['error'] = true;
//            $message['error_type'] = USER_EXISTS;
//            $message['message'] = 'User Already Exists';
//
//            $response->write(json_encode($message));
//
//            return $response
//                ->withHeader('Access-Control-Allow-Origin','*')
//                ->withHeader('Access-Control-Allow-Headers','access')
//                ->withHeader('Access-Control-Allow-Methods','POST')
//                ->withHeader('Access-Control-Allow-Credentials','true')
//                ->withHeader('Content-type', 'application/json')
//                ->withStatus(CONFLICT);
//        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(BAD_REQUEST);


});

$app->post('/sendfcmnotification',function (Request $request,Response $response)
{
    $notificationBody = "Notification From WEB SERVER! Your Child is in Danger.";
    SendFCMNotification($request,$response,$notificationBody);
});

function SendFCMNotification(Request $request,Response $response,$notificationBody)
{

    $firebase = new Firebase();
    $push = new Push();

    // optional payload
    $payload = array();
    $payload['Project'] = 'LAPCS';
    $payload['TeamLead'] = 'Farwa Kazmi';

    // notification title
    $title = 'LAPCS Notification';

    // notification message
    $message = 'Test Message From LAPCS Server';

    // push type - individual / topic
    $push_type = 'individual';

    // whether to include to image or not
    $include_image = TRUE;


    $push->setTitle($title);
    $push->setMessage($message);
    if ($include_image) {
        $push->setImage('https://commons.wikimedia.org/wiki/File:Test.png');
    } else {
        $push->setImage('');
    }
    $push->setIsBackground(FALSE);
    $push->setPayload($payload);


    $json = '';
    $responseFCM = '';

    if ($push_type == 'topic') {
        $json = $push->getPush();
        $responseFCM = $firebase->sendToTopic('LITE-Package', $json);
    } else if ($push_type == 'individual') {
        $json = $push->getPush();
        $regId = 'eSOB0gRzqCY:APA91bE6jx6Sw4PDnsFf1URJMhOeoucz_KiJBFO6AEsi6RYlBUUdslYH2ApNxV7OkWs_MTvna26H-lc4MkPMBrUFNDPK--k96WVfVVZ6DEgb5VWm7PPxOEQOYI1QmjbnIy0lqJN1dpya';
        $responseFCM = $firebase->send($regId, $json,$notificationBody);
    }

    if ($response != '') {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = 'FCM Notification Sent Successfully';
        $response_data['FCM_Response'] = $responseFCM;

        $response->write(json_encode($response_data));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(OK);
    }
    else
    {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'FCM Notification Not Sent';
        $response->write(json_encode($error_detail));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(BAD_REQUEST);
    }

}

function haveEmptyParameters($required_params, $request, $response){
    $error = false;
    $error_params = '';

    $request_params = $request->getParsedBody();

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true;
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

//    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
//    $name = $args['name'];
//    $response->getBody()->write("Hello, $name");
//
//    //Creating Database
//    $db = new DbConnect();
//
//    if($db->getConnection()!=null)
//    {
//        echo "<br/>";
//        echo "Database Connected Successfully";
//    }
//
//    return $response;
//
//});


$app->run();

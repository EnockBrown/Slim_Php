<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require '../vendor/autoload.php';
require __DIR__ . '/../vendor/autoload.php';
require '../includes/DbOperation.php';

$app = new\Slim\App([
   'settings'=>[
    'displayErrorDetails'=>true
   ]

]);



/*
endpoint: create a user
method: POST
*/

$app->post('/register', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('name', 'email', 'password', 'gender','admissionNumber','programe','phone','campus','faculty'),$request,$response)) {
        # code...
        $request_data=$request->getParsedBody();

        $name=$request_data["name"];
        $email=$request_data["email"];
        $password=$request_data['password'];
        $gender=$request_data['gender'];
        $admissionNumber=$request_data['admissionNumber'];
        $programe=$request_data['programe'];
        $phone=$request_data['phone'];
        $campus=$request_data['campus'];
        $faculty=$request_data['faculty'];

        //encrypting passsword
        $has_password=password_hash($password, PASSWORD_DEFAULT);

        $db=new DbOperation;
        $result=$db->registerUser($name,$email,$has_password,$gender,$admissionNumber,$programe,$phone,$campus,$faculty);

        if ($result==USER_CREATED) {
            $message=array();
            $message['error']=false;
            $message['message']='User Created Successfully';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(201);
        }
        elseif ($result==USER_CREATION_FAILED) {
            $message=array();
            $message['error']=true;
            $message['message']='Some error occured';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }
        elseif ($result==USER_EXIST) {
            $message=array();
            $message['error']=true;
            $message['message']='User already exist';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }

    }
                    return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);

});

$app->post('/login', function (Request $request, Response $response) {
   if  (!haveEmptyParameters(array('email','password'),$request,$response)) {
        # code...
     $request_data=$request->getParsedBody();

    $email=$request_data["email"];
    $password=$request_data["password"];

    $db=new DbOperation;
    $result=$db->userLogin($email,$password);

    if ($result==USER_AUTHENTICATED) {
            $user = $db->getUserByEmail($email);
            $response_data = array();
            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
              $response_data['user']=$user; 
           $response_data['user']=$user; 
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200); 

    }
    else if($result == USER_NOT_FOUND){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == USER_PASSWORD_DONOT_MATCH){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential check your username and password';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }

     }

                    return $response
                    ->withHeader('Content-type','application/json')
                    ->withStatus(422);
});


//metod for getting all users
$app->get('/users', function (Request $request, Response $response){
    $db=new DbOperation;
    $users=$db->getAllUsers();

    $response_data=array();
    $response_data['error']=false;
    $response_data['users']=$users;
    $response->write(json_encode($response_data));

                   return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(200);
 

});

//route to delete a unit
$app->DELETE('/drop_unit/{unique_id}', function (Request $request, Response $response,array $args){
        $unique_id=$args['unique_id'];

        $db = new DbOperation; 
        $messages=$db->dropUnit($unique_id);
           
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['message'] = "Unit Removed Successflly";
            //$response_data['messages'] = $messages; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 


});

//ROUTE for getting all EXAMS
$app->get('/exams', function (Request $request, Response $response){
    $db=new DbOperation;
    $exams=$db->getExams();

    $response_data=array();
    $response_data['error']=false;
    $response_data['exams']=$exams;
    $response->write(json_encode($response_data));

                   return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(200);
});
  

$app->post('/update/{unique_id}',function(Request $request,Response $response,array $args){
    $unique_id=$args['unique_id'];
    if (!haveEmptyParameters(array('name','email','gender','admissionNumber','phone','campus','faculty'),$request,$response)) {
        # code...
        $request_data=$request->getParsedBody();

        $name=$request_data["name"];
        $email=$request_data["email"];
        $gender=$request_data['gender'];
        $admissionNumber=$request_data['admissionNumber'];
        $phone=$request_data['phone'];
        $campus=$request_data['campus'];
        $faculty=$request_data['faculty'];
        
        $db = new DbOperation; 
        if($db->updateProfile($name, $email, $gender, $admissionNumber,$phone,$campus,$faculty,$unique_id)){
             $user = $db->getUserByEmail($email);
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['message'] = 'User Updated Successfully';
        //    $user = $db->getUserByEmail($email);
            //$response_data['user'] = $user; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
        
        }else{
            $response_data = array(); 
            $response_data['error'] = true; 
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  

        }

    }
});

//route to sendmessage t a particular user
$app->post('/sendmessage', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('from', 'to', 'title','message'),$request,$response)) {
        # code...
        $request_data=$request->getParsedBody();

        $from=$request_data["from"];
        $to=$request_data["to"];
        $title=$request_data['title'];
        $message=$request_data['message'];

        $db=new DbOperation;
        $result=$db->sendMessage($from, $to, $title, $message);

        if ($result==SENT_SUCCESSFULLY) {
            $message=array();
            $message['error']=true;
            $message['message']='message sent Successfully';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }
        elseif ($result==NOT_SENT_TRY_AGAIN) {
            $message=array();
            $message['error']=false;
            $message['message']='Message not sent try agin leter.';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }

    }
});

//method to enroll for an exam

$app->post('/unit_enrollment', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('from', 'unit', 'unit_code','admissionNumber','faculty','contacts'),$request,$response)) {
        # code...
        $request_data=$request->getParsedBody();
 
        $from=$request_data["from"];
        $unit=$request_data["unit"];
        $unit_code=$request_data['unit_code'];
        $admissionNumber=$request_data['admissionNumber'];
         $faculty=$request_data['faculty'];
          $contacts=$request_data['contacts'];

        $db=new DbOperation;
        $result=$db->unit_enrollment($from,$unit, $unit_code, $admissionNumber, $faculty,$contacts);

        if ($result==SENT_SUCCESSFULLY) {
            $message=array();
            $message['error']=false;
            $message['message']='Enrolled  Successfully';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }
        elseif ($result==NOT_SENT_TRY_AGAIN) {
            $message=array();
            $message['error']=false;
            $message['message']='Message not sent try agin leter.';

            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(422);
        }

    }
});

//route to get messahes fro a particular user
$app->get('/messages/{id}',function(Request $request,Response $response,array $args){
    $userid=$args['id'];
 
        
        $db = new DbOperation; 
        $messages=$db->getMessages($userid);
           
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['messages'] = $messages; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 

});

//route for getting exam enrollment for a student
$app->get('/unit_registration/{id}',function(Request $request,Response $response,array $args){
    $userid=$args['id'];
 
        
        $db = new DbOperation; 
        $units=$db->getUnitEnrollmentForaStudent($userid);

           
            $response_data = array(); 
            $response_data['error'] = false; 
            //$user = $db->getUserByEmail($email);
            $response_data['subjects'] = $units; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 

});

//route for getting exam enrollment for a student
$app->get('/mysubjects/{faculty}',function(Request $request,Response $response,array $args){
    $faculty=$args['faculty'];
 
        
        $db = new DbOperation; 
        $subjects=$db->getMySubjects($faculty);

           
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['units'] = $subjects; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 

});

//route for getting units enrolled by a student
$app->get('/my_units/{from_users_id}',function(Request $request,Response $response,array $args){
    $from_users_id=$args['from_users_id'];
 
        
        $db = new DbOperation; 
        $subjects=$db->getUnitEnrollmentForaStudent($from_users_id);

           
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['units'] = $subjects; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 

});

//ROUTE for getting all FILES
$app->get('/allfiles', function (Request $request, Response $response){
    $db=new DbOperation;
    $files=$db->getAllFiles();

    $response_data=array();
    $response_data['error']=false;
    $response_data['files']=$files;
    $response->write(json_encode($response_data));

                   return $response
                        ->withHeader('Content-type','application/json')
                        ->withStatus(200);
});

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

$app->run();
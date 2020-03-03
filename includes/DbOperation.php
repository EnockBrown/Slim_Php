<?php
 
class DbOperation
{
    private $con;
 
    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }
 
    //Method to create a new user
    function registerUser($name, $email, $password, $gender,$admissionNumber,$programe,$phone,$campus,$faculty)
    {
        if (!$this->isUserExist($email)) {
            //$password = md5($pass);
              $unique_id=uniqid('',true);
            $stmt = $this->con->prepare("INSERT INTO users (unique_id,name, email, password, gender,admissionNumber,programe,phone,campus,faculty,created_at) VALUES (?,?, ?, ?,?, ?,?,?,?,?, NOW())");
            $stmt->bind_param("sssssssiss", $unique_id,$name, $email,$password, $gender,$admissionNumber,$programe,
                $phone,$campus,$faculty);
            if ($stmt->execute())
                return USER_CREATED;
            return USER_CREATION_FAILED;
        }
        return USER_EXIST; 
    }
 
    //Method for user login
    function userLogin($email, $password){

            $stmt = $this->con->prepare("SELECT email FROM users WHERE email = ? ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            // echo $email;
            // echo $password;
            //return $stmt->num_rows > 0;
  
          # code...
         //$hashed_pass=password_hash($password, PASSWORD_DEFAULT)
          $hashed_password=$this->getUsersPasswordByEmail($email);
         // echo $hashed_password;

          if (password_verify($password, $hashed_password)) {
              # code...
            return USER_AUTHENTICATED;
          }
          else
          {
            return USER_PASSWORD_DONOT_MATCH;
          }

    }

 
 //function to get users password by Email
    function getUsersPasswordByEmail($email){
        $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password);
        $stmt->fetch();
      // echo "$password";
     //  echo $this->userLogin($email,$pass);
        return $password;

    }
    //Method to send a message to another user
    function sendMessage($from, $to, $title, $message)
    {
        $stmt = $this->con->prepare("INSERT INTO messages (from_users_id, to_users_id, title, message) VALUES (?, ?, ?, ?);");
        $stmt->bind_param("iiss", $from, $to, $title, $message);
        if ($stmt->execute())
            return true;
        return false;
    }

  
    //method to enroll for exams
    function unit_enrollment($from,$unit, $unit_code, $admissionNumber, $faculty,$contacts)  {
       $unique_id=uniqid('',true);
        $stmt = $this->con->prepare("INSERT INTO exam_enrollment (unique_id,unique_users_id,unit, unit_code, admissionNumber, faculty) VALUES (?,?, ?,?, ?, ?);");
        $stmt->bind_param("sissss",$unique_id, $from, $unit,$unit_code, $admissionNumber, $faculty);
        if ($stmt->execute())
            return true;
        return false;  
    }
 
    //Method to update profile of user
    function updateProfile( $name, $email,  $gender,$admissionNumber,$phone,$campus,$faculty,$unique_id) {
        //$password = md5($pass);
        $stmt = $this->con->prepare("UPDATE users SET name = ?, email = ?,  gender = ?,
        admissionNumber = ?,phone = ?,campus = ?,faculty = ? WHERE unique_id = ?");
        $stmt->bind_param("ssssissi", $name, $email, $gender,$admissionNumber,$phone,
            $campus,$faculty,$unique_id);
        if ($stmt->execute())
            return true;
        return false;
    }

    //function to drop a unit
    function dropUnit($unique_id)
     {
         $stmt = $this->con->prepare("DELETE FROM exam_enrollment WHERE unique_id = ?;");
         $stmt->bind_param("s",$unique_id);
        if ($stmt->execute())
            return true;
        return false;
     }
 
    //Method to get messages of a particular user
    function getMessages($userid)
         {
            $stmt = $this->con->prepare("SELECT messages.id,
             (SELECT users.name FROM users WHERE users.id = messages.from_users_id) as `from`,
              (SELECT users.name FROM users WHERE users.id = messages.to_users_id) as `to`,
               messages.title, messages.message, messages.sentat FROM messages WHERE messages.to_users_id = ?;");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $stmt->bind_result($id, $from, $to, $title, $message, $sent);
     
            $messages = array();
     
            while ($stmt->fetch()) {
                $temp = array();
     
                $temp['id'] = $id;
                $temp['from'] = $from;
                $temp['to'] = $to;
                $temp['title'] = $title;
                $temp['message'] = $message;
                $temp['sent'] = $sent;
     
                array_push($messages, $temp);
            }
     
            return $messages;
    }


         
    //method to get exam enrollment for a particular student
        function getUnitEnrollmentForaStudent($from_users_id)
            {
                $stmt = $this->con->prepare("SELECT unique_id,unique_users_id, unit,unit_code, admissionNumber,faculty FROM exam_enrollment WHERE unique_users_id = ?;");
                $stmt->bind_param("i", $from_users_id);
                $stmt->execute();
                $stmt->bind_result($unique_id, $from_users_id, $unit, $unit_code, $admissionNumber, $faculty);
         
                $units = array();
         
                while ($stmt->fetch()) {
                    $temp = array();
         
                     $temp['unique_id'] = $unique_id;
                     //$temp['from'] = $from_users_id;
                    $temp['name'] = $unit;
                     $temp['code'] = $unit_code;
                    // $temp['admissionNumber'] = $admissionNumber;
                    // $temp['faculty'] = $faculty;
         
                    array_push($units, $temp);
                }
         
                return $units;
    }

        //method to get subjects for a certain faculty
        function getMySubjects($faculty)
            {
                $stmt = $this->con->prepare("SELECT id,Name, Code,Faculty, Clas,Reg_Date FROM subjects WHERE faculty = ?;");
                $stmt->bind_param("s", $faculty);
                $stmt->execute();
                $stmt->bind_result($id, $name, $code, $faculty, $class, $reg_date);
          
                $subjects = array();
         
                while ($stmt->fetch()) {
                    $temp = array();
                    
                    $temp['id'] = $id;
                    $temp['code'] = $code;
                    $temp['name'] = $name;
         
                    array_push($subjects, $temp);
                }
         
                return $subjects;
            }

 
    //Method to get user by email
    function getUserByEmail($email)
        {
            $stmt = $this->con->prepare("SELECT id,unique_id, name, email,gender,admissionNumber,programe,phone,campus,faculty,created_at,updated_at
             FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id,$unique_id, $name, $email, $gender,$admissionNumber,$programe,$phone,$campus,$faculty,$created_at,$updated_at);
            $stmt->fetch();
            $user = array();
           $user['id'] = $id;
             $user['unique_id'] = $unique_id;
            $user['name'] = $name;
            $user['email'] = $email;
            $user['gender'] = $gender;
            $user['admissionNumber'] = $admissionNumber;
            $user['programe'] = $programe; 
             $user['phone'] = $phone; 
            $user['campus'] = $campus;
             $user['faculty'] = $faculty;
            // $user['created_at'] = $created_at;
            //  $user['updated_at'] = $updated_at;
            return $user;
        }
     
    //Method to get all users
    function getAllUsers(){
        $stmt = $this->con->prepare("SELECT id, name, email, gender,admissionNumber,phone,campus,faculty FROM users");
            $stmt->execute();
            $stmt->bind_result($id, $name, $email, $gender,$admissionNumber,$phone,$campus,$faculty);
            $users = array();
            while($stmt->fetch()){
                $temp = array();
                $temp['id'] = $id;
                $temp['name'] = $name;
                $temp['email'] = $email;
                $temp['gender'] = $gender;
                $temp['admissionNumber'] = $admissionNumber;
                $temp['phone'] = $phone;
                $temp['campus'] = $campus;
                $temp['faculty'] = $faculty;
                array_push($users, $temp);
            }
            return $users;
        }

    //method to get all exams
    function getExams()
        {
                 $stmt = $this->con->prepare("SELECT  id, name, passmark, department FROM exams");
                $stmt->execute();
                $stmt->bind_result( $id, $name, $passmark, $department);
               $exams = array();
                while($stmt->fetch()){
                    $temp = array();
                    $temp['id'] = $id;
                    $temp['name'] = $name;
                    $temp['passmark'] = $passmark;
                    $temp['department'] = $department;
                    array_push($exams, $temp);
                    // $temp=[
                    //     'name'=>$name,
                    //     'passmark'=>$passmark,
                    //     'department'=>$department
                    // ];
                    // array_push($exams, $temp);
                }
                return $exams;

        }
 
    //Method to check if email already exist
    function isUserExist($email)
        {
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }


    function getAllFiles(){
        //$stmt=$this->con->prepare("SELECT id, description,message, url FROM images ORDER BY id DESC");
        $stmt=$this->con->prepare("SELECT id, description,message, url FROM images ");
        $stmt->execute();
        $stmt->bind_result($id, $desc,$message, $url);
        $images = array();

        while($stmt->fetch()){
            $temp=array();
            $absurl = 'http://' . gethostbyname(gethostname()) . '/retrofit/includes' . $url;
           $temp['id'] = $id;
           $temp['desc'] = $desc;
           $temp['message'] = $message;
            $temp['url'] = $absurl;
            array_push($images, $temp);
        }

        return $images;
    }

}
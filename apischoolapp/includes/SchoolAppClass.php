<?php

require 'includes/database.php';

Class SchoolAppClass {

    private static $instance;
    private $dbh;
    private $api_token = 'c5bfec4bd898edb36a87a16ed24d543e';
    
    
    static function set_instance() {
        if (null === static::$instance) {
            static::$instance = new static(); //__CLASS;
        }
        return static::$instance;
    }

    function __construct($request = null) {
        $obj_dbh = new database();
        $this->dbh = $obj_dbh->dbh;
    }

    function get_all_users() {
        $data = $this->dbh->query('SELECT * from users')->fetchObject();
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = TRUE;
        $res_arr['msg'] = '';
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function login($email, $password) {
        
        $query = "SELECT * from users where password = '$password' AND email = '$email'";
        //echo $query;
        $res = $this->dbh->query($query)->fetchObject();
        $success = FALSE;
        $data = array();
        $msg = 'Invalid email or password!';
        if ($res) {
            $data = $res;
            $success = TRUE;
            $msg = 'Logged in Successfully.';
        }
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function prepare_attributes($attribute_arr, $separator = ', ', $special_condition = '1') {
        unset($attribute_arr['token']);
        unset($attribute_arr['device']);
        unset($attribute_arr['platform']);
        $attributes = '';
        foreach ($attribute_arr as $key => $value) {
        if($key == 'class_assoc' || $key == 'content_class') {
                $value = trim($value, ',');
        }
            if($value == '')
                continue;
            $attributes .= $key . "='" . addslashes($value) . "' ".$separator." ";
        }
        if(trim($separator) != 'AND') {
           $special_condition = '';
        }
        //echo trim($attributes, "$separator "); exit;
        return trim($attributes.$special_condition, "$separator ");
    }
    
    function prepare_attributes_log($attribute_arr, $separator = ', ') {
        unset($attribute_arr['token']);
        $attributes = '';
        foreach ($attribute_arr as $key => $value) {
            if($key == 'class_assoc') {
                $value = trim($value, ',');
            }
            $attributes .= $key . "='" . $value . "' ".$separator." ";
        }
        //echo trim($attributes, "$separator "); exit;
        return trim($attributes, "$separator ");
    }

    function get_data_by_table($tbl_name, $condition) {
        $query = "SELECT * FROM " . $tbl_name . " WHERE $condition";
        $data = $this->dbh->query($query)->fetchAll(PDO::FETCH_CLASS);
        return $data;
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function create_user($attribute_arr = array()) {
         if(!isset($attribute_arr['user_id']) && !isset($attribute_arr['password'])) {
            $attribute_arr['password'] = $this->randomPassword();
         }
        $attributes_str_insert = $this->prepare_attributes($attribute_arr);
        $fields['email'] = $attribute_arr['email'];
        $condition = $this->prepare_attributes($fields, ' AND ');
        $tbl_name = 'users';
        $success = FALSE;
        $data = array();
        if(isset($attribute_arr['user_id'])) {
           $cond_arr['user_id'] = $attribute_arr['user_id'];
           $res = $this->update_data($attributes_str_insert, $tbl_name, $cond_arr); 
           $msg = 'User updated Successfully.';
           $data = $res;
                        $success = TRUE;
        }
        else {
                $exist = $this->get_data_by_table($tbl_name, $condition);
                if (!$exist) {
                    $res = $this->insert_data($attributes_str_insert, $tbl_name, 'user_id');
                    if ($res) {
                        $body = "Dear ".$attribute_arr['name'].", Your Password is: ".$attribute_arr['password'];
                        $subject = "Hi ".$attribute_arr['name'].", your account has been created with ".$_SERVER['HTTP_HOST'];
                        $mail_res = $this->send_mail($attribute_arr['email'], $subject, $body);
                        $data = $res;
                        $success = TRUE;
                        $msg = 'User created Successfully.';
                    }
                } else {
                    $msg = 'User aleready exist.';
                }
        }
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function insert_data($attributes, $tbl_name, $pk) {
        $query = "INSERT INTO " . $tbl_name . " SET " . $attributes;
       //echo $query; exit;
        $res = $this->dbh->query($query);
        $res_arr = false;
        if ($res) {
            $fields = array($pk => $this->dbh->lastInsertId());
            $condition = $this->prepare_attributes($fields, 'AND');
            $res_arr = $this->get_data_by_table($tbl_name, $condition);
        }

        return $res_arr;
    }
    
    function update_data($attributes, $tbl_name, $update_cond) {
        $update_cond = $this->prepare_attributes($update_cond, 'AND');
        $query = "UPDATE " . $tbl_name . " SET " . $attributes." WHERE $update_cond";
        $res = $this->dbh->query($query);
        $res_arr = false;
        if ($res) {
            $res_arr = $this->get_data_by_table($tbl_name, $update_cond);
        }

        return $res_arr;
    }
    
    function delete_data($attributes, $tbl_name) {
        $update_cond = $this->prepare_attributes($attributes, 'AND');
        $query = "DELETE FROM " . $tbl_name ." WHERE $update_cond";
        //echo $query; exit;
        $res = $this->dbh->query($query);
        $res_arr = false;
        if ($res) {
            $res_arr = true;
        }

        return $res_arr;
    }
    
    function delete_user($attributes) {
       $update_cond = array('user_id' => $attributes['user_id']);
       $res = $this->delete_data($update_cond, 'users');
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $res;
        $res_arr['msg'] = '';
        $res_arr['data'] = array();
        
        return $res_arr;
    }
    
    function delete_content($attributes) {
       $update_cond = array('content_id' => $attributes['content_id']);
       $res = $this->delete_data($update_cond, 'contents');
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $res;
        $res_arr['msg'] = '';
        $res_arr['data'] = array();
        
        return $res_arr;
    }
    
    function send_mail($to, $subject, $body) {
        //return true;
        $res = mail($to, $subject, $body);
        return $res;
        require_once "Mail.php";

        $from = '<aabshar.forever@gmail.com>'; //change this to your email address
        $subject = 'Hi, You have successfully registered.'; // subject of mail
        $headers = array(
            'From' => $from,
            'To' => $to,
            'Subject' => $subject
        );

        $smtp = Mail::factory('smtp', array(
                    'host' => 'ssl://smtp.gmail.com',
                    'port' => '465',
                    'auth' => true,
                    'username' => 'aabshar.forever@gmail.com', //your gmail account
                    'password' => 'aabshar@1991#' // your password
        ));
// Send the mail
        $mail = $smtp->send($to, $headers, $body);
    }
    
    function list_users($attribute_arr) {
        $condition = $this->prepare_attributes($attribute_arr, 'AND');
        $condition .= " order by created desc";
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $success = false;
        $msg = 'No user found';
        $data = array();
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = 'User populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function list_data($attribute_arr) {
       // print_r($attribute_arr);
        $special_cond = '';
        if(isset($attribute_arr['is_class'])) {
            $special_cond = "content_class IS NOT NULL";
            unset($attribute_arr['is_class']);
        }
        else
        {
            $special_cond = "content_class IS NULL";
        }
        $condition = $this->prepare_attributes($attribute_arr, 'AND', $special_cond);
        $condition .= " order by added_date desc";
        $tbl_name = 'contents';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $success = false;
        $msg = 'No data found.';
        $data = array();
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = 'Data populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function search_user($search_txt, $user_type) {
        //$condition = $this->prepare_attributes($attribute_arr, 'AND');
        $condition = "user_type = '".$user_type."' AND (name like '%".$search_txt."%' OR email like '%".$search_txt."%' OR class_assoc like '%".$search_txt."%') order by created desc";
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $success = false;
        $msg = 'No user found';
        $data = array();
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = 'Match found.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function search_data($search_txt, $content_type, $is_class = 0) {
        $special_cond = '';
        if($is_class) {
            $special_cond = " AND content_class IS NOT NULL";
        }
        $condition = "content_type = '".$content_type."' AND (title like '%".$search_txt."%' OR added_date like '%".$search_txt."%') $special_cond order by added_date desc";
       
        $tbl_name = 'contents';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $success = false;
        $msg = 'No match found';
        $data = array();
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = 'Match found.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function get_custom_format_data($exist, $type, $single_row = 0) {
        $data = array();
        if($type == 5) {
            foreach($exist as $line) {
                $row['notificationId'] = $line->content_id;
                $row['notificationTitle'] = $line->title;
                $row['notificationDescription'] = strip_tags($line->description);
                $row['notificationType'] = $line->content_type;
                $row['notificationDate'] = date("dMY",strtotime($line->added_date));
                $data[] = $row; 
            }
        }
        else if($type == 3) {
             foreach($exist as $line) {
                $row['newsId'] = $line->content_id;
                $row['newsHeading'] = $line->title;
                $row['newDescription'] = strip_tags($line->description);
                $row['newsUrl'] = $line->video_url;
                $row['contentUrl'] = $line->content_url;
                $row['thumbnailUrl'] = $this->get_thumbnail_url($line->video_url);
                $row['newsDate'] = date("dMY",strtotime($line->added_date));
                $data[] = $row; 
            }
           // print_r($data);
            if($single_row)
            $data = array(current($data));
        }
        else if($type == 4) {
             foreach($exist as $line) {
                $row['infoId'] = $line->content_id;
                $row['infoHeading'] = $line->title;
                $row['infoDescription'] = strip_tags($line->description);
                $row['infoUrl'] = $line->content_url;
                $row['infoDate'] = date("dMY",strtotime($line->added_date));
                $data[] = $row; 
            }
             if($single_row)
            $data = array(current($data));
        }
        else if($type == 1) {
             foreach($exist as $line) {
                $row['assignmentId'] = $line->content_id;
                $row['assignmentHeading'] = $line->title;
                $row['assignmentDescription'] = strip_tags($line->description);
                $row['assignmentUrl'] = $line->content_url;
                $row['videoUrl'] = $line->video_url;
                $row['thumbnailUrl'] = $this->get_thumbnail_url($line->video_url);
                $row['assignmentDate'] = date("dMY h:i A",strtotime($line->added_date));
                $data[] = $row; 
            }
             if($single_row)
            $data = array(current($data));
        }

        return $data;
    }

    function get_data($type, $post_data = array()) {
        $attribute_arr['content_type'] = $type;
        $attribute_arr['status'] = '1';
        $special_cond = '';
        if(isset($post_data['class'])) {
           $special_cond = " find_in_set(".$post_data['class'].", content_class)"; 
        }
        else {
           $special_cond .= "content_class IS NULL";
           //$attribute_arr['content_class'] = '';
        }
        $single_row = 0;
        if($type == 1) {
            $single_row = 1;
        }
        else if($type == 3) {
            $single_row = 1;
        }
        else if($type == 4 && !isset($post_data['class'])) {
            $single_row = 1;
        }
        //echo $special_cond; exit;
        $condition = $this->prepare_attributes($attribute_arr, 'AND', $special_cond);
        $condition .= " order by added_date desc";
        $tbl_name = 'contents';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No data found';
        
        if ($exist) {
            $data = $this->get_custom_format_data($exist, $type, $single_row);
            $success = TRUE;
            $msg = 'Data populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function get_classroom_info($attribute_arr) {
        $fields['user_id'] = $attribute_arr['user_id'];
        $condition = $this->prepare_attributes($fields, 'AND');
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No user found';
        $i = 1;
        $class_rooms = '';
        while($i<=10) {
            $class_rooms .= $i.",";
            $i++;
        }
        //print_r($exist);
        if ($exist) {
            $data = array('active_class_rooms' => $exist[0]->class_assoc, 'all_class_rooms' => trim($class_rooms,','));
            //$data = $exist;
            $success = TRUE;
            $msg = 'Data populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function get_user_info($attribute_arr) {
        $fields['user_id'] = $attribute_arr['user_id'];
        $condition = $this->prepare_attributes($fields, 'AND');
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No user found';
        $i = 1;
        $class_rooms = '';
        while($i<=10) {
            $class_rooms .= $i.",";
            $i++;
        }
        //print_r($exist);
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = 'Data populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function get_schools($attribute_arr) {
        $fields['user_id'] = $attribute_arr['user_id'];
        $fields['user_type'] = 5;
        $condition = $this->prepare_attributes($fields, 'AND');
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No user found';
        $i = 1;
        
        if ($exist) {
            foreach($exist as $line) {
                $ret_arr[] = array('school_id' => $line->user_id, 'name' => $line->name);
            }
            $data = $ret_arr;
            $success = TRUE;
            $msg = 'Data populated successfully.';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function get_content_info($attribute_arr) {
        $fields['content_id'] = $attribute_arr['content_id'];
        $condition = $this->prepare_attributes($fields, 'AND');
        $tbl_name = 'contents';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No data found';
        $i = 1;
        $class_rooms = '';
        while($i<=10) {
            $class_rooms .= $i.",";
            $i++;
        }
        //print_r($exist);
        if ($exist) {
            $data = $exist;
            $success = TRUE;
            $msg = '';
        } 
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function log_api($post_data, $api_url, $response) {
        $attr_arr['device'] = $post_data['device'];
        $attr_arr['platform'] = isset($post_data['platform']) ? $post_data['platform']: 'desktop';
        $attr_arr['api_url'] = $api_url;
        $attr_arr['response_json'] = json_encode($response);
        $attr_arr['request_json'] = json_encode($post_data);
        $fields = $this->prepare_attributes_log($attr_arr,', ');
        $res = $this->insert_data($fields, 'api_log', 'id');
    }
    
    function save_data($post_data) {
        if(isset($post_data['content_id'])) {
         $update_cond['content_id'] = $post_data['content_id'];
         unset($post_data['content_id']);
         $fields = $this->prepare_attributes($post_data);
         $res = $this->update_data($fields, 'contents', $update_cond);
        $success = false;
        $data = array();
        $msg = 'Error occured during data insertion.';
        if($res) {
            $msg = "Data updated successfully.";
            $success = true;
            $data = $res;
        }   
        }
        else
        {
        $fields = $this->prepare_attributes($post_data);
        $res = $this->insert_data($fields, 'contents', 'content_id');
       // print_r($res);
        $success = false;
        $data = array();
        $msg = 'Error occured during data insertion.';
        if($res) {
            $msg = "Data inserted successfully.";
            $success = true;
            $data = $res;
        }
        
        }
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;
        
        return $res_arr;
    }
    
    function get_thumbnail_url($url) {
       $arr = explode('?v=', $url);
       
       if(isset($arr[1]))
           $url = 'http://img.youtube.com/vi/'.$arr[1].'/0.jpg';
       else
           $url = '';
       return $url;
    }
}

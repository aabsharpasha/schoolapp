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
    
    function update_class_by_device_token($user_id, $school_id, $device_token) {
        $attributes = $this->prepare_attributes(array('user_id' => $user_id, 'school_id' => $school_id));
        $update_cond =array("device_token" => $device_token);
        $this->update_data($attributes, 'devices', $update_cond);
    }
    
    function insert_device_token($attributes_arr) {
        //print_r($attributes_arr);
        $attributes = $this->prepare_attributes($attributes_arr);
        if($this->insert_data($attributes, 'devices', 'user_id')) {
            return TRUE;
        }
    }
    
    function logout($device_token, $to_update = 1) {
        $attributes = $this->prepare_attributes(array('user_id' => NULL, 'school_id' => NULL));
        $update_cond =array("device_token" => $device_token);
       $query = "UPDATE devices SET user_id = NULL, school_id = NULL WHERE device_token = '$device_token'";
       //echo $query;
       if($to_update || 1) {
            $this->dbh->query($query);
        }
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = TRUE;
        $res_arr['msg'] = '';
        $res_arr['data'] = array();

        return $res_arr;
    }
    
    function splash_screen($post_data) {
        $post_data['os_platform'] = $post_data['platform'];
        $attributes = $this->prepare_attributes($post_data);
        $condition = " device_token = '".$post_data['device_token']."'";
        $exist = $this->get_data_by_table('devices', $condition);
        if(!$exist) {
            $this->insert_data($attributes, 'devices', 'device_token');
        }
        
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = TRUE;
        $res_arr['msg'] = '';
        $res_arr['data'] = array();

        return $res_arr;
    }
    
    function login($email, $password, $device_token = '') {
        
        $query = "SELECT * from users where password = '$password' AND email = '$email'";
        
        $res = $this->dbh->query($query)->fetchObject();
        $success = FALSE;
        $data = array();
        $msg = 'Invalid email or password!';
        if ($res) {
            $data = $res;
            $school_info = $this->get_school_info(array('user_id' => $data->user_id));
            $data->school_name = $school_info['data'][0]['name'];
            $success = TRUE;
            $msg = 'Logged in Successfully.';
            if($email != 'admin' && !empty($device_token))
              $this->update_class_by_device_token($res->user_id, $res->school_id, $device_token);
        }
        
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }

    function prepare_attributes($attribute_arr, $separator = ', ', $special_condition = '1') {
      
        if(isset($attribute_arr['token']))
            unset($attribute_arr['token']);
        if(isset($attribute_arr['device']))
            unset($attribute_arr['device']);
        if(isset($attribute_arr['platform']))
            unset($attribute_arr['platform']);
 
        $attributes = '';
        foreach ($attribute_arr as $key => $value) {
        if($key == 'class_assoc' || $key == 'content_class') {
                $value = trim($value, ',');
        }
            if(!isset($value))
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
        $device_token = $attribute_arr['device_token'];
        unset($attribute_arr['device_token']);
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
                        if($device_token) {
                            $condition = " device_token = '".$device_token."'";
                            $is_exist = $this->get_data_by_table('devices', $condition);
                            if(!$is_exist) {
                                $attribute_arr_token = array('user_id' => $res[0]->user_id, 'device_token' => $device_token, 'os_platform' => $attribute_arr['platform'], 'school_id' => $res[0]->school_id);
                                $dev_token_res = $this->insert_device_token($attribute_arr_token);
                            }
                            else
                            {
                                $attribute_arr_token = array('user_id' => $res[0]->user_id, 'os_platform' => $attribute_arr['platform'], 'school_id' => $res[0]->school_id);
                                $dev_token_res = $this->update_class_by_device_token($res[0]->user_id, $res[0]->school_id, $device_token);
                            }
                        }
                        $body = "Dear ".$attribute_arr['name'].", Your Password is: ".$attribute_arr['password'];
                        $subject = "Hi ".$attribute_arr['name'].", your account has been created with ".$_SERVER['HTTP_HOST'];
                        $mail_res = $this->send_mail($attribute_arr['email'], $subject, $body);
                        $data = $res;
                        $school_info = $this->get_school_info(array('user_id' => $res[0]->user_id));
                        $data[0]->school_name = $school_info['data'][0]['name'];
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
        if($attribute_arr['school_id'] == 0) {
            unset($attribute_arr['school_id']);
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
        $condition = "content_type = '".$content_type."' AND (title like '%".$search_txt."%' OR content_time like '%".$search_txt."%' OR content_class like '%".$search_txt."%') $special_cond order by added_date desc";
        
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
                $row['notificationDescription'] = str_replace('&nbsp;',' ',strip_tags($line->description));
                $row['notificationType'] = $line->content_type;
                $row['notificationDate'] = date("dMY",strtotime($line->added_date));
                $data[] = $row; 
            }
        }
        else if($type == 3) {
             foreach($exist as $line) {
                $row['newsId'] = $line->content_id;
                $row['newsHeading'] = (string) $line->title;
                $row['newDescription'] = str_replace('&nbsp;',' ',strip_tags($line->description));
                $row['newsUrl'] = $line->video_url;
                $row['contentUrl'] = $line->content_url;
                $row['videoUrl'] = $line->video_url;
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
                $row['infoHeading'] = (string) $line->title;
                $row['infoDescription'] = str_replace('&nbsp;',' ',strip_tags($line->description));
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
                $row['assignmentHeading'] = (string) $line->title;
                $row['assignmentDescription'] = str_replace('&nbsp;',' ',strip_tags($line->description));
                $row['assignmentUrl'] = $line->content_url;
                $row['videoUrl'] = $line->video_url;
                $row['thumbnailUrl'] = $this->get_thumbnail_url($line->video_url);
                $row['assignmentDate'] = date("dMY h:i A",strtotime($line->added_date));
                $data[] = $row; 
            }
             
        }
        else if($type == 2 || $type == 6) {
             foreach($exist as $line) {
                $row['eventId'] = $line->content_id;
                $row['EventTitle'] = (string) $line->title;
                $row['EventDescription'] = str_replace('&nbsp;',' ',strip_tags($line->description));
                $row['EventDate'] = date("m/d/Y", strtotime($line->content_time));
                $data[] = $row; 
            }
            
        }

        return $data;
    }

    function get_data($type, $post_data = array()) {
        $attribute_arr['content_type'] = $type;
        $attribute_arr['status'] = '1';
        $attribute_arr['school_id'] = $post_data['school_id'];
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
        } else if($type == 2) {
            $date1 = date("Y-m-d",strtotime(" -15 days"));
            $date2 = date("Y-m-d",strtotime(" 15 days"));
            $special_cond .= " AND content_time >= '$date1' AND content_time <= '$date2'";
        }
        //echo $special_cond; exit;
        $condition = $this->prepare_attributes($attribute_arr, 'AND', $special_cond);
        $limit = '';
        if($type == 1 && $_POST['class']) {
            $limit = "limit 0,10";
        }
        $condition .= " order by added_date desc $limit";
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
        //print_r($attribute_arr);
        if($attribute_arr['user_type'] != 3) {
            $fields['user_id'] = $attribute_arr['user_id'];
        }
        
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
    
    function get_school_info($attribute_arr) {
        $fields['user_id'] = $attribute_arr['user_id'];
        $condition = $this->prepare_attributes($fields, 'AND');
        $tbl_name = 'users';
        $exist = $this->get_data_by_table($tbl_name, $condition);
        $data = array();
        
        $success = false;
        $msg = 'No user found';
        $i = 1;
        
        if ($exist) {
            foreach($exist as $line) {
                //echo $line->school_id;
                //print_r($line); exit;
                
                if($line->school_id) {
                    //echo 'hi';
                    $fields['user_id'] = $line->school_id;
                    $condition = $this->prepare_attributes($fields, 'AND');
                    $exist_next = $this->get_data_by_table($tbl_name, $condition);
                    foreach($exist_next as $line) {
                        $ret_arr[] = array('school_id' => $line->user_id, 'name' => $line->name);
                    }
                }
                else
                {
                $ret_arr[] = array('school_id' => $line->user_id, 'name' => $line->name);
                }
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
        //print_r($post_data);
        $msg = 'Error occured during data insertion.';
        if(isset($post_data['content_id'])) {
         $update_cond['content_id'] = $post_data['content_id'];
         unset($post_data['content_id']);
         $fields = $this->prepare_attributes($post_data);
         $res = $this->update_data($fields, 'contents', $update_cond);
        $success = false;
        $data = array();
        
        if($res) {
            $msg = "Data updated successfully.";
            $success = true;
            $data = $res;
        }   
        }
        else
        {
            $fields = $this->prepare_attributes($post_data);
            if($post_data['content_type'] == 2) {
                $arr_class= explode(',',$post_data['content_class']);
                foreach($arr_class as $class) {
                    if(!empty($class)) {
                        $cond_class .= " content_class like '%".trim($class)."%' OR";
                    }
                }
                
                $condition = " content_time = '".$post_data['content_time']."' AND (".trim($cond_class,'OR').") ";
                $exist = $this->get_data_by_table('contents', $condition);
                if(!$exist) {
                    $res = $this->insert_data($fields, 'contents', 'content_id');
                }
                else 
                {
                    $msg = 'Event already exist on this date for the class';  
                }
            } else {
                
                $res = $this->insert_data($fields, 'contents', 'content_id');
            }
            
            /* send notification part */
            $this->send_notification_to_user($post_data);
            /* end of send notification part */
                
                
            $success = false;
            $data = array();
            
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
    
    function send_apn($iphone_push, $post_data) {
		//echo "Iphone: ".json_encode($iphone_push);
		//exit;
		$certificate = 'production.pem';
                //$mode = 'dev1';
                //$certificate = 'development.pem';
		//$passphrase = '12345';
		//print_r($post_data); exit;
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);
		//stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		$class = 0;
                if($post_data['content_class']) {
                    $class = 1;
                }
//1 = notification, 
//2=information, 
//3=Event, 
//4=news,
//5=assignment, 
//6=timetable, 
//7= class notification 
//8= class information
        
        //1:assign, 2: Timetable, 3: News, 4: Information, 5:notification, 6:Event
                if($post_data['content_type'] == 1 && $class) {
                    $notificationType = 5;
                } else if($post_data['content_type'] == 2 && $class) {
                    $notificationType = 6;
                } else if($post_data['content_type'] == 3) {
                    $notificationType = 4;
                } else if($post_data['content_type'] == 4 && $class) {
                    $notificationType = 8;
                } else if($post_data['content_type'] == 4) {
                    $notificationType = 2;
                } else if($post_data['content_type'] == 5 && $class) {
                    $notificationType = 7;
                } else if($post_data['content_type'] == 5) {
                    $notificationType = 1;
                } else if($post_data['content_type'] == 6) {
                    $notificationType = 3;
                }
                
		foreach($iphone_push as $row) {
			$message = strip_tags($post_data['description']);
			$body['aps'] = array(
                            'alert' => strip_tags(str_replace('&nbsp;',' ',$message)),
                            'sound' => 'default', 
                            'class' => $class, 
                            'notificationType' => $notificationType
                        );
			
			$deviceToken = trim($row['device_token']);
                        //echo $deviceToken.'/n';
                        //$deviceToken = 'ef27271a70c38f7a4d16ce8b191d639cce41880526df01e93fddd7b6a928e820';
			if(empty($deviceToken))
				continue;
				
			$payload = json_encode($body);
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
                        if($mode == 'dev') {
                            $fp = stream_socket_client(
					'ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx); 
                        }
                        else {
                         $fp = stream_socket_client(
					'ssl://gateway.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                        
                        }
		        //echo $fp;
			stream_set_blocking ($fp, 0);
                        //echo $fp.'/n';
			if (!$fp) {
                                
				//exit("Failed to connect: $err $errstr" . PHP_EOL);
			}
                                
			$result = fwrite($fp, $msg, strlen($msg));
//                               $apple_error_response = fread($fp, 6);
//                               if(!empty($apple_error_response)) {
//                                    $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);
//                               }
//                               if ($error_response['status_code'] != '0') {    
//                                        $last = $row['id'];
//                                        $iphone_push_new = array_filter(
//                                             $iphone_push,
//                                             function ($value) use($last) {
//                                                 return ($value['id'] > $last);
//                                             }
//                                         );
//                                         //var_dump($iphone_push_new); exit;
//                                         $this->send_apn($iphone_push_new, $post_data);
//                                }
		}
		@fclose($fp);
	}
        
    function send_gcm($gcm_id_array, $message, $post_data) {
            $GCM_KEY = 'AIzaSyDGdYW33MiPztWUgVPzUw1B3As62LyXKnA';
            //$GCM_KEY = 'AIzaSyDbiHs0IV4Q36YPHBHlQ-Ur2JWDSGaXILM';
            
            foreach($gcm_id_array as $line) {
               $registrationIds[] = $line['device_token'];
            }
            
                $class = 0;
                if($post_data['content_class']) {
                    $class = 1;
                }
                
                if($post_data['content_type'] == 1 && $class) {
                    $notificationType = 5;
                } else if($post_data['content_type'] == 2 && $class) {
                    $notificationType = 6;
                } else if($post_data['content_type'] == 3) {
                    $notificationType = 4;
                } else if($post_data['content_type'] == 4 && $class) {
                    $notificationType = 8;
                } else if($post_data['content_type'] == 4) {
                    $notificationType = 2;
                } else if($post_data['content_type'] == 5 && $class) {
                    $notificationType = 7;
                } else if($post_data['content_type'] == 5) {
                    $notificationType = 1;
                } else if($post_data['content_type'] == 6) {
                    $notificationType = 3;
                }
            //print_r($registrationIds);
            $message = strip_tags($post_data['description']);
			$data['aps'] = array(
                            'alert' => strip_tags($message),
                            'sound' => 'Default', 
                            'class' => $class, 
                            'notificationType' => $notificationType
                        );
            //$data = array('aps' => array("alert" => strip_tags($message), "sound" => "Default"));
            $fields = array (
                    'registration_ids' 	=> $registrationIds,
                    'data'	        => $data,
            );
	    //print_r($fields); exit;
            $headers = array (
                    'Authorization: key=' . $GCM_KEY,
                    'Content-Type: application/json'
            );

            $ch = curl_init();

            curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            //var_dump($result);
            curl_close( $ch );

            return $result;
    }

    function get_user_push_arr($data, $platform) {
        $condition = 1;
        $school_id = $data['school_id'];
        if(isset($data['content_class'])) {
            $arr_class = explode(',',$data['content_class']);
            foreach($arr_class as $class) {
                if(!empty($class)) {
                    $cond_class .= " class_assoc like '%".trim($class)."%' OR";
                }
            }
           $condition = trim($cond_class,'OR');
           
           $query = "SELECT d.id, u.user_id, d.user_id, d.device_token, d.os_platform FROM users u LEFT JOIN devices d ON u.user_id = d.user_id WHERE ($condition) AND os_platform = '$platform'  AND d.school_id = '$school_id' order by d.id";
        } 
        else
        {
           $query = "SELECT d.id, d.user_id, d.device_token, d.os_platform FROM devices d WHERE ($condition) AND os_platform = '$platform' AND d.school_id = '$school_id' order by d.id"; 
        }
        //echo $query; exit;
        $record = $this->dbh->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return $record;
    }
    
    function get_classes($attribute_arr) {
        $data = array();
        for($i=1;$i<=10;$i++) {
            $row['id'] = $i;
            $row['name'] = "Class ".$i;
            $data[] = $row;
        }
        $success = true;
        $msg = "Data populated successfully.";
        //print_r($data); exit;
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;

        return $res_arr;
    }
    
    function send_notification_to_user($post_data) {
        //print_r($post_data);
        $push_arr_ios = $this->get_user_push_arr($post_data, 'iOS');
        $push_arr_android = $this->get_user_push_arr($post_data, 'android');

        //print_r($push_arr_android); exit;
        //print_r($push_arr_ios); exit;
        if(count($push_arr_android)) {
           $this->send_gcm($push_arr_android, $post_data['description'], $post_data);
        }
        if(count($push_arr_ios)) {
            $this->send_apn($push_arr_ios, $post_data);
        }
    }
    
    function get_notification_details($post_data) {
        foreach($post_data as $key => $line) {
           if(is_numeric($key)) {
                $post_data['content_type'] = $key;
                $data[$key] = $this->get_notification_info($line, $post_data); 
           }
        }
        $success = true;
        $msg = "Data populated successfully.";
        //print_r($data); exit;
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = $success;
        $res_arr['msg'] = $msg;
        $res_arr['data'] = $data;
        
      return $res_arr;  
    }

    function get_notification_info($line, $post_data) {
        
        //1 = notification, 
//2=information, 
//3=Event, 
//4=news,
//5=assignment, 
//6=timetable, 
//7= class notification 
//8= class information
        $class = $post_data['class'];
        $school_id = $post_data['school_id'];
        $last_id = $line;
        if($post_data['content_type'] == 5 && $class) {
            $notificationType = 1;
        } else if($post_data['content_type'] == 6 && $class) {
            $notificationType = 2;
        } else if($post_data['content_type'] == 4) {
            $notificationType = 3;
        } else if($post_data['content_type'] == 8 && $class) {
            $notificationType = 4;
        } else if($post_data['content_type'] == 2) {
            $notificationType = 4;
        } else if($post_data['content_type'] == 7 && $class) {
            $notificationType = 5;
        } else if($post_data['content_type'] == 1) {
            $notificationType = 5;
        } else if($post_data['content_type'] == 3) {
            $notificationType = 6;
        }
        if($class) {
            $condition = "AND content_class like '%$class%' and school_id = '$school_id'";
        }
        else
        {
            $condition = "AND content_class IS NULL and school_id = '$school_id'";
        }
        $query = "SELECT count(content_id) as count FROM
             contents WHERE content_id > '$last_id'
             AND content_type = '$notificationType' $condition";
        //echo $query;
        $data =  $this->dbh->query($query)->fetchAll(PDO::FETCH_CLASS);
        //var_dump($data); exit;
        if($data[0]->count) {
            return $data[0]->count;
        }
 else {
     return 0;
 }
        //print_r($res_arr); exit;
    }
}

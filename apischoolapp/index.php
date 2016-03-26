<?php
error_reporting(E_ALL & ~E_NOTICE);
require 'vendor/autoload.php';
require 'includes/SchoolAppClass.php';
define('API_TOKEN','c5bfec4bd898edb36a87a16ed24d543e');
$app = new \Slim\App;

function validate_user($request) {
   if($request['token'] != API_TOKEN && $request['device'] != 'desktop') {
        $res_arr['status'] = 'Ok';
        $res_arr['is_success'] = false;
        $res_arr['msg'] = 'Unauthorised access';
        $res_arr['data'] = array();
        //echo 'Unauthorised access';
        echo json_encode($res_arr);
        exit;
    }
}

// route middleware for simple API authentication
function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    if (API_TOKEN != $_POST['token']) {
      $app->halt(401);
    }
}
//print_r($app->request()); exit;
//echo "hi".$app->request->getUri(); exit;
$app->post('/login', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
   $username = $post_data['username'];
   $password = $post_data['password'];
   
   $obj = SchoolAppClass::set_instance();
   $response = $obj->login($username, $password, $post_data['device_token']);
   $obj->log_api($post_data, $_SERVER['REQUEST_URI'], $response);
   if($response['is_success']) {
      // echo "test";
       //session_start();
       $_SESSION['logged_in'] = 1;
       //echo $_SESSION['logged_in'];
   }
   echo json_encode($response);
});

$app->post('/create_user', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->create_user($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/list_users', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->list_users($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/list_data', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->list_data($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/delete_user', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->delete_user($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/delete_content', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->delete_content($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_data/{type}', function ($request, $response, $args)  {
   $post_data = $request->getParsedBody();
   //print_r($post_data); exit;
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_data($args['type'], $post_data);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/save_data', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->save_data($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response, JSON_NUMERIC_CHECK);
});

$app->post('/get_classroom_info', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_classroom_info($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_content_info', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_content_info($request->getParsedBody());
   //print_r($response);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_user_info', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_user_info($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/search_user', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
   $obj = SchoolAppClass::set_instance();
   $response = $obj->search_user($post_data['search_txt'], $post_data['user_type']);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/search_data', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
   $obj = SchoolAppClass::set_instance();
   $response = $obj->search_data($post_data['search_txt'], $post_data['user_type'], isset($post_data['is_class']));
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/update_data', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
   $username = $post_data['username'];
   $email = $post_data['password'];
   $obj = SchoolAppClass::set_instance();
   $response = $obj->login($username, $email);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/splash_screen', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
  // print_r($post_data); exit;
   $obj = SchoolAppClass::set_instance();
   $response = $obj->splash_screen($post_data);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/logout', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $post_data = $request->getParsedBody();
  // print_r($post_data); exit;
   $obj = SchoolAppClass::set_instance();
   if($post_data['email'] == 'admin') {
       $is_update = 0;
   }
   $response = $obj->logout($post_data['device_token'], $is_update);
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_schools', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_schools($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_school_info', function ($request, $response, $args)  {
    validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_school_info($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_classes', function ($request, $response, $args)  {
   //print_r($request->getParseBody());
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_classes($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});

$app->post('/get_notification_details', function ($request, $response, $args)  {
   validate_user($request->getParsedBody());
   $obj = SchoolAppClass::set_instance();
   $response = $obj->get_notification_details($request->getParsedBody());
   $obj->log_api($request->getParsedBody(), $_SERVER['REQUEST_URI'], $response);
   echo json_encode($response);
});
$app->run();

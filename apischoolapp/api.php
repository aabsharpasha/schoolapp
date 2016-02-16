<?php session_start();
if((isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] == TRUE) || $_REQUEST['action']== 'login')
{
$curl_handle = curl_init();
$post_arr = file_get_contents('php://input');
$post_arr = json_decode($post_arr, true);
curl_setopt($curl_handle, CURLOPT_URL, 'http://api.schoolapp/' . $_REQUEST['action']);
curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
if ($_REQUEST['is_post']) {
    curl_setopt($curl_handle, CURLOPT_POST, 1);
}

curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_arr['postData']);
$buffer = curl_exec($curl_handle);
curl_close($curl_handle);
if (empty($buffer)) {
    print "Nothing returned from url.<p>";
} else {
    if($_REQUEST['action'] == 'login') {
        $buffer = json_decode($buffer);
        if($buffer->is_success == true){
            $_SESSION['is_logged_in'] = true;
        }
        $buffer = json_encode($buffer);
    }
    echo $buffer;
    
}
}
else
{
    $buffer['is_success'] = 'false';
    echo json_encode($buffer);
}
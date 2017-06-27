<?php 
/**
 * 使用Get的方式返回：challenge和capthca_id 此方式以实现前后端完全分离的开发模式 专门实现failback
 * @author Tanxu
 */
//error_reporting(0);

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
require_once dirname(__FILE__) . '/lib/geetestlib.php';
global $_G;
//读缓存信息
$captchaid = $_G['cache']['plugin']['geetest3']['captchaid'];
$privatekey = $_G['cache']['plugin']['geetest3']['privatekey'];
$GtSdk = new GeetestLib($captchaid, $privatekey);
$model = $_GET['model'];


$data = array(
		"user_id" => $_G['uid'], # 网站用户id
	);

if($model == "validate"){

	    $result = $GtSdk->success_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode'], $data);

	    if ($result) {
	    	$data_array = array("status"=>"success");
	    	echo json_encode($data_array);
	    } else{
	    	$data_array = array("status"=>"fail");
	    	echo json_encode($data_array);
	    }
}else{

	$status = $GtSdk->pre_process($data, 1);
	echo $GtSdk->get_response_str();
}

 ?>
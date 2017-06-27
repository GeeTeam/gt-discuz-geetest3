<?php
/**
 *  [极验手机版验证码(geetest.{modulename})] (C)2015-2099 Powered by geetest Inc..
 *  Version: 1.0
 *  Date: 2017-6-23 17:43
 */

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
C::import('geetestlib','plugin/geetest3/lib');

class mobileplugin_geetest3 {
    public $captcha_allow = false;
    public $mobile ;  
    public $mod = array();
    public $captcha = '';
    public $private = '';
    public function mobileplugin_geetest3(){
        global $_G;
        //读缓存信息
        $this->mod = unserialize($_G['cache']['plugin']['geetest3']['mod']);
        $this->mobile = $_G['cache']['plugin']['geetest3']['mobile']; 
        $this->captchaid = $_G['cache']['plugin']['geetest3']['captchaid'];
        $this->privatekey = $_G['cache']['plugin']['geetest3']['privatekey'];

        if(in_array($_G['groupid'], unserialize($_G['cache']['plugin']['geetest3']['groupid'])) && $this->mobile ){
            $this->captcha_allow = true;
        }

        $post_count = $_G['cookie']['pc_size_c'];
        if($post_count == null){
            $arr = array('a','b','c','d','e','f');
            shuffle($arr);
            $post_count = '0'.explode($arr);
            dsetcookie('pc_size_c', $post_count, 24*60*60);
        }else{
            $post_count = intval($post_count);
            $post_num = intval($_G['cache']['plugin']['geetest3']['post_num']);
            
            if(($post_num != 0 && $post_count >= $post_num)){
                $this->captcha_allow = false;
            }
        }

    }

    # 判断模块
    public function _cur_mod_is_valid(){
        $cur = CURMODULE;
        switch(CURMODULE){
            case "logging":
                $cur = "2";
                break;
            case "register":
                $cur = "1";
                break;
            case "post": //论坛模块
                if($_GET["action"] =="reply"){
                    $cur = "4";
                }else if($_GET["action"] =="newthread"){
                    $cur = "3";
                }else if($_GET["action"] =="edit"){
                    $cur = "5";
                }
                break;
            case "forumdisplay":
            case "viewthread":
                $cur = "4";
                break;
        }
        return in_array($cur, $this->mod);
    }


    public function _code_output($form,$button,$cp_button){
        if( !($this->_cur_mod_is_valid() && $this->captcha_allow ) ){
            return ;
        }

    #初始化验证
    $html = <<<JS
        <script type="text/javascript" src="source/plugin/geetest3/js/gt.js"></script>
        <script type="text/javascript" src="source/plugin/geetest3/js/mobile_init.js"></script>

        <script type="text/javascript">
            var form = '$form'; 
            var button = '$button'; 
            var cp_button = '$cp_button'; 
            InitCaptcha(form,button,cp_button);

        </script>
JS;
    return $html;
    }



    #判断验证是否通过
    public function geetest_validate($challenge, $validate, $seccode, $data=array()) {
        $geetest = new geetestlib($this->captchaid,$this->privatekey);
        return $geetest->success_validate($challenge, $validate, $seccode, $data=array());
    }


    public function fix_register(){
        return '<script id="testScript" type="text/javascript" src="source/plugin/geetest3/js/geetest_mobile.js" data-btn="btn_register" data-form="registerform"></script>';

    }
    
    # 注册验证 放在底部嵌入点
    public function global_footer_mobile(){
        if (CURMODULE == 'register' &&  $this->_cur_mod_is_valid() && $this->captcha_allow ) {
            return $this->fix_register().$this->_code_output('#registerform','membersubmit','cp_membersubmit'); 
        }else{
            return ;
        }
    }


}

class mobileplugin_geetest3_member  extends mobileplugin_geetest3{

    public function fix_login(){
        return '<script id="testScript" type="text/javascript" src="source/plugin/geetest3/js/geetest_mobile.js" data-btn="btn_login" data-form="loginform"></script>';
    }

    public function logging_bottom_mobile(){
        if( !($this->_cur_mod_is_valid() && $this->captcha_allow ) ){
            return ;
        }else{
            return $this->fix_login().$this->_code_output('#loginform','membersubmit','cp_membersubmit'); 
        }

    }


    # 登录判断
    public function logging_code() {
        global $_G;
            if($_GET['action'] == "logout"){
            return;
        }

        if($this->_cur_mod_is_valid() && $this->captcha_allow) {
            if(submitcheck('loginsubmit', 1, $seccodestatus) && empty($_GET['lssubmit'])) {//
                                   
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);
                    if($response != 1){//
                        if($response == -1){
                            showmessage(lang('plugin/geetest', 'seccode_invalid'));
                        }else if($response == 0){
                            showmessage( lang('plugin/geetest', 'seccode_expired') );
                        }
                    }
                }
            }
        }

    # 注册判断
    public function register_code(){
        global $_G;
        if($this->_cur_mod_is_valid() && $this->captcha_allow) {
            if(submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)){
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);

                if($response != 1){//
                    if($response == -1){
                        showmessage(lang('plugin/geetest', 'seccode_invalid'));
                    }else if($response == 0){
                        showmessage( lang('plugin/geetest', 'seccode_expired') );
                    }
                }
            }
        }
    }
}

class mobileplugin_geetest3_forum extends mobileplugin_geetest3 {

    public function fix_viewthread(){
        return '<script id="testScript" type="text/javascript" src="source/plugin/geetest3/js/geetest_mobile.js" data-btn="fastpostsubmit" data-form="fastpostsubmitline"></script>';
    }

    public function fix_post(){
        return '<script id="testScript" type="text/javascript" src="source/plugin/geetest3/js/geetest_mobile.js" data-btn="postsubmit" data-form="y"></script>';
    }

    //手机底部回复
    public function viewthread_fastpost_button_mobile(){
        if ( !($this->_cur_mod_is_valid() && $this->captcha_allow ) ) {
            return;
        }else{
            return $this->fix_viewthread().$this->_code_output('#fastpostform','fastpostsubmit','cp_fastpostsubmit');   
        }


    }   
    
         //手机跳转回复及发帖
    public function post_bottom_mobile(){
        if (CURMODULE == "post" && $this->_cur_mod_is_valid() && $this->captcha_allow) {
            return $this->fix_post().$this->_code_output('#postform','postsubmit','cp_postsubmit');     
        }else{
            return;
        }
    }



    public function post_rccode() {
        global $_G;
        $success = 0;
        if($this->_cur_mod_is_valid() && $this->captcha_allow) {
            if(submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('editsubmit', 0, $seccodecheck, $secqaacheck) ) {
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);
                if($response != 1){
                    if($response == -1){
                        showmessage(lang('plugin/geetest', 'seccode_invalid'));
                    }else if($response == 0){
                        showmessage( lang('plugin/geetest', 'seccode_expired') );
                    }
                }else{
                    $success == 1;
                }
            }
        }
        
        if($success == 1){
            $post_count = $_G['cookie']['pc_size_c'];
            $post_count = intval($post_count);
            $post_count = ($post_count + 1);
            $arr = array('a','b','c','d','e','f');
            shuffle($arr);
            $post_count = $post_count.implode("",$arr);
            dsetcookie('pc_size_c',  $post_count);
        }
    }
}



?>  
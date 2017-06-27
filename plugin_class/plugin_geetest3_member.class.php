<?php


   
class plugin_geetest3_member  extends plugin_geetest3{  

    function register_input_output(){    
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = "register";
            if($_GET["infloat"] == "yes"){
                $gt_geetest_id = "gt_float_register_input";
                $page_type = "register_float";
            }else{
                $gt_geetest_id = "gt_page_register_input";
                $page_type = "register";
            }
            return $this->_code_output($cur_mod, $gt_geetest_id, $page_type);
       }   

    }
        
    function logging_input_output() {
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = "logging";
            if($_GET["infloat"] == "yes"){
                $gt_geetest_id = "gt_float_logging_input";
                $page_type = "logging_float";
            }else{
                $gt_geetest_id = "gt_page_logging_input";
                $page_type = "logging";
            }
            return $this->_code_output($cur_mod, $gt_geetest_id, $page_type);
        }
    }

    

    function register_code(){
        global $_G;
        $cur = CURMODULE;
        if($this->_cur_mod_is_valid() && $this->captcha_allow && $cur == "register") {
            if(submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)){
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);
                if($response != 1){
                    if($response == -1){
                        showmessage(lang('plugin/geetest', 'seccode_invalid'));
                    }else if($response == 0){
                        showmessage( lang('plugin/geetest', 'seccode_expired') );
                    }
                }
            }       
        }
    }
    function logging_code() {
        if($_GET['action'] == "logout"){
            return;
        }
        $cur = CURMODULE;
        if ($this->open && $this->logging_mod_valid()) {
            if($_GET['username'] != "" && $_GET['password'] != "" && $_GET['lssubmit'] == "yes"){
                if(( $_GET['geetest_validate'] == null && $_GET['geetest_seccode'] == null) || 
                    ($_GET['geetest_validate'] == "" && $_GET['geetest_seccode'] == "")){
                    $this->_show();
                    return;
                }
            }
        }else{
            return;
        }

        if( ! $this->has_authority() ){
            return;
        }

        global $_G;
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
  
    public function _show(){
         include template('common/header_ajax');
         $js = <<<HTML

 <script type="text/javascript">
    var handler = function (captchaObj) {

        captchaObj.onReady(function () {
            $("header-loggin-btn").click();
        }).onSuccess(function () {
            captchaObj.bindForm('#lsform');
            document.getElementsByClassName("vm")[2].click();
        });         
        var btn = document.getElementById('header-loggin-btn');
        
        console.log(btn);
            btn.addEventListener('click',function () {
                // debugger;
            captchaObj.verify();

        })
     };
    var xmlHttp;
    function createxmlHttpRequest() {
        if (window.ActiveXObject) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        } else if (window.XMLHttpRequest) {
            xmlHttp = new XMLHttpRequest();
        }
    }
    createxmlHttpRequest();
    xmlHttp.open("GET", "./plugin.php?id=geetest3&model=start");
    xmlHttp.send(null);
    xmlHttp.onreadystatechange = function(result) {
        if ((xmlHttp.readyState == 4) && (xmlHttp.status == 200)) {
                var obj = JSON.parse(xmlHttp.responseText);          
                console.log(obj);
                    initGeetest({
                        gt: obj.gt,
                        challenge: obj.challenge,
                        offline: !obj.success,
                        timeout: '5000',
                        product: "bind", // 产品形式，包括：float，popup
                        width: "300px"
                    }, handler);
        }
    }
         </script>
HTML;
        echo($js);
         include template('common/footer_ajax');
         dexit();
    }
 




    function has_authority(){
        //针对掌上论坛不需要验证
        if( $_GET['mobile'] == 'no' && $_GET['submodule'] == 'checkpost' ){
            return false;
        }
        return true;
    }

}

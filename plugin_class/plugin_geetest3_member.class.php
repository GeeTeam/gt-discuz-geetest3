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
<script type="text/javascript" reload="1"> 
if (window.__gtcaptch__) {
            window.__gtcaptch__.onSuccess(function () {
            window.__gtcaptch__.bindForm('#lsform');
            document.getElementsByClassName("vm")[2].click();
        });
    window.__gtcaptch__.verify();
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

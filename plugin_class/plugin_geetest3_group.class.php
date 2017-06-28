<?php 

class plugin_geetest3_group  extends plugin_geetest3{  
    
    function post_middle(){
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = 'newthread';
            $page_type = 'newthread_reply_grade';
            $gt_geetest_id = 'gt_post_middle';
            return $this->_code_output($cur_mod ,$gt_geetest_id, $page_type);
        }
    }



    function post_infloat_middle() { 
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = 'newthread';
            $page_type = 'newthread_reply_float';
            $gt_geetest_id = 'gt_post_infloat_middle';
            return $this->_code_output($cur_mod, $gt_geetest_id, $page_type);
        }
    }

    function viewthread_fastpost_content () {
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = 'reply';
            $page_type = 'reply';
            $gt_geetest_id = 'gt_viewthread_fastpost_content';
            return $this->_code_output($cur_mod, $gt_geetest_id, $page_type).$this->_fix_fastpost_btn_extra_pos($gt_geetest_id);
        }
    }

    function _fix_fastpost_btn_extra_pos($gt_geetest_id) {
        $output = <<<JS
    <script type="text/javascript">
        function move_geetest_before_submit() {
            var gt_submitBtn = $('fastpostsubmit');
            var geetest = $('$gt_geetest_id');
            gt_submitBtn.parentNode.insertBefore(geetest, gt_submitBtn);
            //_attachEvent(gt_submitBtn, 'click', GeeTestWidget.call_refresh);
        }
        _attachEvent(window, 'load', move_geetest_before_submit);

        function get_button(){
            var b = [];
            var buttons = document.getElementsByTagName("button")
            for(var i=0; i<buttons.length; i++){
                var button = buttons[i];
                if(button.type == "submit"){
                    b.push(button)
                }
            }   
            return b;           
        }

        window.gt_custom_ajax = function (status, $) {
            function refresh(){
                setTimeout(function(){
                    $(".gt_refresh_button").click();
                },3000);
            }
            if(status) {
              var buttons = get_button();
              for(var i in buttons){
                _attachEvent(buttons[i], 'click', refresh);
              }
              
            }
         }
     </script>
JS;
        return $output;
    }

    //修复快速回复，包含到form表单中
    function _fix_fast_reply_pos($gt_geetest_id){
         $output = <<<JS
    <script type="text/javascript">
        function move_fast_geetest_before_submit() {
            var vfastposttb = $('vfastposttb');
            var geetest = $('$gt_geetest_id');
            vfastposttb.parentNode.insertBefore(geetest, vfastposttb);

            geetest.style.backgroundColor="white";
            geetest.style.marginTop="-20px";
            geetest.style.marginLeft="-3px";
            geetest.style.marginRight="-3px";
            geetest.style.marginBottom="3px";
                
            $('vfastpost').style.marginTop = "60px";    
        }
        _attachEvent(window, 'load', move_fast_geetest_before_submit);

    </script>
JS;
        return $output;
    }

    function _fix_zhibo_reply($gt_geetest_id) {
        $output = <<<JS
    <script type="text/javascript">
        function move_fast_geetest_before_submit() {
            if($('livereplysubmit')){
                var livereplysubmit = $('livereplysubmit');
                var geetest = $('$gt_geetest_id');
                livereplysubmit.parentNode.insertBefore(geetest, livereplysubmit);
            }
        }
        _attachEvent(window, 'load', move_fast_geetest_before_submit);
    </script>
JS;
        return $output;
    }

    //直播贴回复
    function forumdisplay_postbutton_top(){
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = 'popup';
            $btn_id = "livereplysubmit";
            $gt_geetest_id = 'gt_forumdisplay_postbutton_top';
            return $this->_code_output($cur_mod, $gt_geetest_id, "", $btn_id).$this->_fix_zhibo_reply($gt_geetest_id);
        }

    }
    
    //页面底部发帖
    function forumdisplay_fastpost_btn_extra() {
        if ($this->_cur_mod_is_valid()) {
            $cur_mod = 'newthread';
            $page_type = 'newthread';
            $gt_geetest_id = 'gt_forumdisplay_fastpost_btn_extra';
            return $this->_code_output($cur_mod, $gt_geetest_id, $page_type).$this->_fix_fastpost_btn_extra_pos($gt_geetest_id);
        }
    }

    //快速回复
    function viewthread_modaction(){
        //2.5版本不存在快速回复
        include_once(DISCUZ_ROOT.'/source/discuz_version.php');
        //其他版本
        global $_G;
        
        $allowfastreply = $_G['setting']['allowfastreply'] && $_G['group']['allowpost'];
        //快速回复是否开启,并且有发帖权限
        if(DISCUZ_VERSION != "X2.5" && $allowfastreply == 1){
            $cur_mod = 'reply';
            $gt_geetest_id = 'gt_viewthread_modaction';
            return $this->_code_output($cur_mod, $gt_geetest_id).$this->_fix_fast_reply_pos($gt_geetest_id);
        }

    }

    
    //处理发帖/恢复/编辑验证
    function post_recode() {
        global $_G;
        if( ! $this->has_authority() ){
            return;
        }
        $success = 0;
        
        if($this->_cur_mod_is_valid() && $this->captcha_allow) {
            if(submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('editsubmit', 0, $seccodecheck, $secqaacheck) ) {
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);
                if($response != 1){//
                    if($response == -1){
                        showmessage(lang('plugin/geetest', 'seccode_invalid'));
                    }else if($response == 0){
                        showmessage( lang('plugin/geetest', 'seccode_expired') );
                    }
                }else{
                    $success = 1;
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

    //判断是否有权限发帖留言，或者其他
    function has_authority(){
        //2.5版本不存在快速回复
        global $_G;
        include_once(DISCUZ_ROOT.'/source/discuz_version.php');
        if(DISCUZ_VERSION == "X2.5" && $_GET['handlekey'] == "vfastpost"){
             return false ;
        }
        //针对掌上论坛不需要验证
        if( $_GET['mobile'] == 'no' && $_GET['module'] == 'sendreply' ){
            return false;
        }
        if( $_GET['mobile'] == 'no' && $_GET['module'] == 'newthread' ){
            return false;
        }
        //针对广播，回复不好验证。有三处回复
        if( $_GET['action'] == 'reply' && $_GET['inajax'] == '1' &&  $_GET['handlekey'] != 'reply' &&  $_GET['infloat'] != 'yes'){
            return false;
        }
        $action = $_GET['action'];
        //快速回复是否开启,并且有发帖权限,日志
        if($action == 'newthread' && $_G['group']['allowpost'] != 1 ){//发帖
            return false;
        }else if($action == 'reply' && $_G['group']['allowreply'] != 1 ){//回复
            return false;
        }
        return true;
    }
}

 ?>
<?php


class plugin_geetest3_home extends plugin_geetest3 {
    //修正验证条的位置
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

    </script>
JS;
		return $output;
	}
	//包含到form表单中
	function _fix_fast_form_pos($gt_geetest_id, $fastpostform){
         $output = <<<JS
    <script type="text/javascript">
        function move_fast_geetest_before_submit() {
            var fastpostform = $('$fastpostform');
            var geetest = $('$gt_geetest_id');
            fastpostform.insertBefore(geetest, fastpostform.firstChild);
            		
            geetest.style.marginBottom="10px";
         		
        }
        _attachEvent(window, 'load', move_fast_geetest_before_submit);

    </script>
JS;
		return $output;
	}
	//修复支付提交
	function _fix_pay_submit($gt_geetest_id) {
		$output = <<<JS
    <script type="text/javascript">
        function move_geetest_before_submit() {
            var gt_submitBtn = $('addfundssubmit_btn');
            var geetest = $('$gt_geetest_id');
            gt_submitBtn.parentNode.insertBefore(geetest, gt_submitBtn);
        }
        _attachEvent(window, 'load', move_geetest_before_submit);

    </script>
JS;
		return $output;
	}

	//支付
	function spacecp_credit_bottom(){
		if ($this->_cur_mod_is_valid()) {
			$cur_mod = 'popup';
			$btn_id = "addfundssubmit_btn";
			$gt_geetest_id = 'gt_spacecp_credit_bottom';
			if ($_GET['op'] == 'buy') {
				return $this->_code_output($cur_mod, $gt_geetest_id, "", $btn_id).$this->_fix_pay_submit($gt_geetest_id);
			}
		}
	}
	//广播
	function follow_top() {
		if ($this->_cur_mod_is_valid()) {
			$cur_mod = 'follow';
			$gt_geetest_id = 'gt_follow_top';
			$page_type = 'follow';
			return $this->modify_follow_css().$this->_code_output($cur_mod, $gt_geetest_id, $page_type).$this->_fix_fast_form_pos($gt_geetest_id, 'fastpostform');
		}
	}
            function modify_follow_css(){
                $css = <<<html
                <style>
                .mn {overflow: initial !important;}
                </style>
html;
                return $css;
            }
	//日志
	function spacecp_blog_middle() {
		if ($this->_cur_mod_is_valid()) {
			$cur_mod = 'blog';
			$gt_geetest_id = 'gt_spacecp_blog_middle';
			$page_type = 'blog';
			return $this->_code_output($cur_mod, $gt_geetest_id, $page_type);
		}
	}
	//日志评论
	function space_blog_face_extra() {
		if ($this->_cur_mod_is_valid()) {
			$cur_mod = 'popup';
			$gt_geetest_id = 'gt_space_blog_face_extra';
			$btn_id = "commentsubmit_btn";
			return $this->_code_output($cur_mod, $gt_geetest_id, "",$btn_id);
		}
	}
	
	function space_wall_face_extra(){
		if ($this->_cur_mod_is_valid()) {
			$cur_mod = "popup";
			$gt_geetest_id = "gt_space_wall_face_extra";
			$btn_id = "commentsubmit_btn";
			return $this->_code_output($cur_mod, $gt_geetest_id,"", $btn_id);     
		}
	}
    //处理广播、日志验证
    function spacecp_follow_recode(){
    	
    	$this->spacecp_recode();
    }
    function spacecp_blog_recode(){
    	$this->spacecp_recode();
    }
    function spacecp_comment_recode(){
    	
    	$this->spacecp_recode();
    }
	
	function spacecp_recode() {
		if( ! $this->has_authority() ){
            return;
		}
        global $_G;
		$success = 0;
		
		if($this->_cur_mod_is_valid() && $this->captcha_allow) {
			
			if(submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('blogsubmit', 0, $seccodecheck, $secqaacheck)) {
                $response = $this->geetest_validate($_GET['geetest_challenge'], $_GET['geetest_validate'], $_GET['geetest_seccode']);
				if($response != 1){
					if($response == -1){
						showmessage(lang('plugin/geetest3', 'geetest_error1'));
					}else if($response == 0){
						showmessage( lang('plugin/geetest3', 'geetest_error2') );
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
		//针对掌上论坛不需要验证
        if( $_GET['mobile'] == 'no' && $_GET['submodule'] == 'checkpost' ){
            return false;
        }
        
		global $_G;
		
        $action = $_GET['ac'];
        //快速回复是否开启,并且有发帖权限,日志
        if($action == 'follow' && $_G['group']['allowpost'] != 1 ){//发帖
            return false;
        }else if($action == 'blog' && $_G['group']['allowblog'] != 1 ){//回复
			return false;
        }else if($action == 'comment' && $_G['group']['allowcomment'] != 1 ){//回复
			return false;
        }

        return true;
	}
	

}

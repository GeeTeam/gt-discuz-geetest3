<?php

// error_reporting(E_ERROR);

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');

C::import('geetestlib', 'plugin/geetest3/lib');

class plugin_geetest3{
    public $captcha_allow = false;
    public $mods = array();
    public $keyset = array();
    public $style = array();
    public $config = array();
    public $data = array();
    public $open;
    
    function plugin_geetest3() {
        global $_G;
        //读缓存信息
        $this->mods = unserialize($_G['cache']['plugin']['geetest3']['mod']);
        $this->open = $_G['cache']['plugin']['geetest3']['open'];
      
        $this->captchaid = $_G['cache']['plugin']['geetest3']['captchaid'];
        $this->privatekey = $_G['cache']['plugin']['geetest3']['privatekey'];
        $this->data = array(
            "user_id" => $_G['uid'], # 网站用户id
        );
        // $this->style = $_G['cache']['plugin']['geetest'];
        
        //初始化
        if ($this->open == '1') {
            
            //登陆注册不需要选择用户组
            if (CURMODULE == "logging" || CURMODULE == "register") {
                $this->captcha_allow = true;
            } 
            else if (in_array($_G['groupid'], unserialize($_G['cache']['plugin']['geetest3']['groupid']))) {
                $this->captcha_allow = true;
            } 
            else {
                $this->captcha_allow = false;
            }
        } 
        else {
            $this->captcha_allow = false;
        }
        
        //发帖大于限定数，则不用插件
        $post_count = $_G['cookie']['pc_size_c'];
        if ($post_count == null) {
            $arr = array('a', 'b', 'c', 'd', 'e', 'f');
            shuffle($arr);
            $post_count = '0' . explode($arr);
            dsetcookie('pc_size_c', $post_count, 24 * 60 * 60);
        } 
        else {
            $post_count = intval($post_count);
            $post_num = intval($_G['cache']['plugin']['geetest3']['post_num']);
            if ($post_num != 0 && $post_count >= $post_num) {
                $this->captcha_allow = false;
            }
        }
        // var_dump($_G['group']);
    }


    //修复QQ互联注册
    function _fix_register($gt_geetest_id) {
        $output = <<<JS
    <script type="text/javascript">
        function move_fast_geetest_before_submit() {
            var registerformsubmit = $('registerformsubmit');
            var geetest = $('$gt_geetest_id');
            registerformsubmit.parentNode.insertBefore(geetest, registerformsubmit);
            $('$gt_geetest_id').style.marginBottom = "20px";
        }
        _attachEvent(window, 'load', move_fast_geetest_before_submit);
    </script>
JS;
        return $output;
    }

    
    function global_login_extra() {
        global $_G;
        if ($_G['uid'] == '1') {
            return;
        } 
        else if ($_G['uid'] == '0' && $this->logging_mod_valid()) {
$html = <<<HTML
        <script type="text/javascript" src="source/plugin/geetest3/js/gt3-init.js"></script>
        <script type="text/javascript" src="source/plugin/geetest3/js/gt.js"></script>

        <script type="text/javascript">
            var lsform = document.getElementById('lsform');
            var o = document.createElement("button");  
            o.id = "header-loggin-btn";       
            o.setAttribute('type', 'submit');                               
            o.value = ""; 
            o.style.display="none";
            lsform.appendChild(o);
        </script>
        <script type="text/javascript">
            var handler = function (captchaObj) {
                window.__gtcaptch__ = captchaObj;         
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
            xmlHttp.open("GET", "./plugin.php?id=geetest3&model=start&t=" + (new Date()).getTime());
            xmlHttp.send(null);
            xmlHttp.onreadystatechange = function(result) {
                if ((xmlHttp.readyState == 4) && (xmlHttp.status == 200)) {
                        var obj = JSON.parse(xmlHttp.responseText);          
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
        return $html;
    }
}

    
    //QQ互联注册嵌入点
    public function global_header() {
        $cur = CURMODULE;
        if ($cur == "connect" && $this->_cur_mod_is_valid()) {
            $cur_mod = "popup";
            $gt_geetest_id = "gt_global_header";
            $btn_id = "registerformsubmit";
            return $this->_code_output($cur_mod, $gt_geetest_id, '', $btn_id) . $this->_fix_register($gt_geetest_id);
        }


    }



    
    public function _code_output($cur_mod = '', $geetest_id = 'gt_geetest', $page_type = '', $param = '') {
        
        // if (!($this->_cur_mod_is_valid())) {
        //     return;
        // }


        if (!$this->captcha_allow) {
            return;
        }
        global $_G;
        
        $output = '';
        $cur_mod = empty($cur_mod) ? CURMODULE : $cur_mod;
        // $style = $this->getStyle($page_type);
        $geetestlib = new geetestlib($this->captchaid, $this->privatekey);


        $status = $geetestlib->pre_process($this->data, 1);

        $dict = json_decode($geetestlib->get_response_str(),true);


        if ($status) {
            
            switch ($cur_mod) {
                case 'register':
                case 'logging':


                    $output = " <div  class='rfm'><table><tbody><tr><th><div>*&#34892;&#20026;&#39564;&#35777;:</div></th><td id='$geetest_id'>";
                    $output.= $this->get_widget($geetest_id);
                    $output.= '</td></tr></tbody></table></div>';
                    break;

                case 'newthread':
                case 'reply':
                case 'edit':
                    $output = "<div id='$geetest_id' style='margin-bottom:10px;'>";
                    $output.= $this->get_widget($geetest_id);
                    $output.= '</div>';
                    break;

                case 'blog':
                case 'follow':
                case 'comment':
                    $output = "<div id='$geetest_id'>";
                    
                    $output.= $this->get_widget($geetest_id);
                    $output.= '</div>';
                    break;

                case 'popup':
                    $output = "<div id='$geetest_id'>";
                    $output.= $this->get_widget($geetest_id);
                    $output.= '</div>';
                    break;
            }
            
            return $output;
        } 
        else {
            return;
        }
    }

    

    
    public function logging_mod_valid() {
        $mod = "2";
        return in_array($mod, $this->mods);
    }
    
    public function _cur_mod_is_valid() {
        $cur = CURMODULE;
        switch (CURMODULE) {
            case "logging":
                $mod = "2";
                break;

            case "register":
                $mod = "1";
                break;

            case "post":
                if ($_GET["action"] == "reply") {
                    $mod = "4";
                } 
                else if ($_GET["action"] == "newthread") {
                    $mod = "3";
                } 
                else if ($_GET["action"] == "edit") {
                    $mod = "5";
                }
                break;

            case "forumdisplay":
                $mod = "3";
                break;

            case "viewthread":
                $mod = "4";
                break;

            case "follow":
                $mod = "6";
                break;

            case "spacecp":
                if ($_GET["ac"] == "blog") {
                    $mod = "7";
                }
                if ($_GET["ac"] == "comment") {
                    $mod = "8";
                }
                if ($_GET["ac"] == "follow") {
                    $mod = "6";
                }
                if ($_GET["ac"] == "credit") {
                    $mod = "9";
                }
                break;

            case "space":
                if ($_GET["do"] == "wall") {
                    $mod = "8";
                }
                if ($_GET["do"] == "blog" || $_GET["do"] == "index") {
                    $mod = "7";
                } 
                else {
                    $mod = "4";
                }
                
                break;

            case "connect":
                $mod = "1";
                break;

            case "index":
                $mod = "2";
                break;

            default:
                return 1;
            }

            return in_array($mod, $this->mods);
        }
        
        // private function getStyle($page_type) {
        //     $style_str = $style_str = $this->style[$page_type];
            
        //     $style_arr = explode(" ", $style_str);
        //     $top = $style_arr[0] == "auto" ? "auto" : $style_arr[0] . 'px ';
        //     $bottom = $style_arr[1] == "auto" ? "auto" : $style_arr[1] . 'px ';
        //     $left = $style_arr[2] == "auto" ? "auto" : $style_arr[2] . 'px ';
        //     $right = $style_arr[3] == "auto" ? "auto" : $style_arr[3] . 'px';
        //     $margin = "margin:" . $top . ' ' . $right . ' ' . $bottom . ' ' . $left;
        //     return $margin;
        // }



    public function get_widget($element) {

    $html = <<<JS
        <script type="text/javascript" src="source/plugin/geetest3/js/gt3-init.js"></script>
        <script type="text/javascript" src="source/plugin/geetest3/js/gt.js"></script>

        <script type="text/javascript" reload="1">
            var geetest = '$element';
            InitCaptcha(geetest);
        </script>
JS;
    return $html;
    }


    public function geetest_validate($challenge, $validate, $seccode) {
        $geetest = new geetestlib($this->captchaid,$this->privatekey);
        // $geetest->set_keyset($this->keyset);
        return $geetest->success_validate($challenge, $validate, $seccode,$this->data);
    }

}





include ('plugin_class/plugin_geetest3_member.class.php');

include ('plugin_class/plugin_geetest3_forum.class.php');

include ('plugin_class/plugin_geetest3_home.class.php');

include ('plugin_class/plugin_geetest3_group.class.php');
?>
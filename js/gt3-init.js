
var InitCaptcha = function(element,callback) {
    var handlerEmbed = function (captchaObj) {

        // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
        var a = $(element);
        captchaObj.appendTo(a);
        // captchaObj.onReady(function () {
        //     $("#wait")[0].className = "hide";
        // });
        // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
		if (element == "gt_forumdisplay_postbutton_top" || element == "gt_viewthread_fastpost_content" || element=="gt_viewthread_modaction") {
				    captchaObj.onSuccess(function () {
				    		setTimeout(function(){captchaObj.reset()},5000);
				        });	
			}


    };


    var loadGeetest = function(config) {
             initGeetest({
                gt: config.gt,
                challenge: config.challenge,
                new_captcha: config.new_captcha,
                product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                offline: !config.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
            }, handlerEmbed);
    }

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
            if (obj.success == 1) {

                loadGeetest(obj);

            }
        }
    }
}
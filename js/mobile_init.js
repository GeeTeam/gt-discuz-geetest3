
var InitCaptcha = function(form,button,cp_button,callback) {
    var handlerEmbed = function (captchaObj) {

        // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
        // captchaObj.appendTo(element);
        captchaObj.onReady(function () {
            
        }).onSuccess(function () {
            captchaObj.bindForm(form);
        	document.getElementById(button).click();
        });     
        var btn = document.getElementById(cp_button);
        // btn.click(function () {
        //     captchaObj.verify();
        // })
        btn.addEventListener('click',function(){
        	captchaObj.verify();
        })
    };


    var loadGeetest = function(obj) {
             initGeetest({
                gt: obj.gt,
                challenge: obj.challenge,
                offline: !obj.success,
                timeout: '5000',
                product: "bind", // 产品形式，包括：float，popup
                width: "300px"
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
                // var obj = xmlHttp.responseText;
             // var obj = eval('(' + result.target.response + ')');
            if (obj.success == 1) {

                loadGeetest(obj);

            }
        }
    }
}
var data_btn = document.getElementById('testScript').getAttribute('data-btn');
var data_form = document.getElementById('testScript').getAttribute('data-form');
    if (data_form == "registerform" || data_form =="loginform") {
        var form = document.getElementById(data_form);
        var btn = document.getElementsByClassName(data_btn)[0];
    }else if(data_form == "y"){
        var parent = document.getElementsByClassName(data_form)[0];
        var btn = document.getElementById(data_btn);
    }else if (data_form == "fastpostsubmitline"){
        var parent = document.getElementById(data_form);
        var btn = document.getElementById(data_btn);
    }
window.addEventListener('load',function(){
    if (data_form == "registerform" || data_form =="loginform") {
        var formdialog = btn.firstChild;
        var b = formdialog.cloneNode(true);
        b.setAttribute('type','button');
        b.id = "cp_membersubmit"
        formdialog.style.display = 'none';
        formdialog.id = "membersubmit";
        btn.appendChild(b);
        // var geetest = document.getElementsByClassName("geetest")[0];
        // form.appendChild(geetest);
    }else if (data_form == "y"){
        var b = btn.cloneNode(true);
        b.setAttribute('id','cp_postsubmit');
        b.setAttribute('class','btn_pn btn_pn_blue');
        b.setAttribute('disable', 'false');
        btn.style.display = "none";
        parent.appendChild(b);
        // var geetest = document.getElementsByClassName("geetest")[0];
    }else if (data_form == "fastpostsubmitline"){
        var b = btn.cloneNode();
        b.setAttribute('id','cp_fastpostsubmit');
        b.setAttribute('class','button2');
        btn.style.display = "none";
        parent.appendChild(b);
        // var geetest = document.getElementsByClassName("geetest")[0];
        // parent.appendChild(geetest);
    }
        b.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // document.getElementById("fastpostsubmit").click();
            // geetest.style.display = "block";
        }, false)
    });
    // window.gt_custom_ajax = function(result, id, message) {
    //   if(result) {
    //     document.getElementById(id).parentNode.parentNode.style.display = "none";
    //     if (data_form == "registerform" || data_form =="loginform") {
    //         btn.firstChild.click();
    //     }else{
    //         btn.click();
    //     }
    //     window.GeeTest[0].refresh();
    //   }
    // }
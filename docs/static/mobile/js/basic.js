/**
 * Created by Dennis on 2015/12/30.
 */


$(function(){
    /*投票选中效果*/
    $(".voting-checkbox").click(function(){
        $(this).toggleClass("active")
    });

    $(".voting-radio").click(function(){
        $(this).closest(".voting-main").children(".voting-cell").children(".voting-radio").removeClass("active");
        $(this).addClass("active");
    });

    /*返回顶部*/
    $(function () {
        $(window).scroll(function(){
            if ($(window).scrollTop()>100){
                $("#back-to-top").fadeIn();
            }
            else
            {
                $("#back-to-top").fadeOut();
            }
        });

        //当点击跳转链接后，回到页面顶部位置

        $("#back-to-top").click(function(){
            $('body,html').animate({scrollTop:0},500);
            return false;
        });
    });

    /* 创建投票增加、删除选项功能 */
    // var i = 3;
    var question = 3;
    $(document).on('click','.option-add',function(){
        var questionId = $(this).attr("question-id");
        $(this).closest("p").prev(".vote-option").append(
            '<div class="form-group bdb-grey">'
            +'<div class="input-group-sm position-rel">'
            +' <input type="text" name="question['+questionId+'][answer][]" class="form-control vote-check p-r-md" placeholder="选项（24字以内）">'
            +'<a href="javascript:void(0)" class="close-input"><i class="icon-close10"></i></a>'
            +'</div>'
            +'</div>'
        );
        // i++;
        $(".close-input").click(function(){
            $(this).closest(".form-group").remove();
        });
    });
    

    $(".add-question").click(function(){
        $(this).closest(".container").before(
        '<div class="container bg-white m-t-4">'

        +'<div class="form-group bdb-grey">'
        +'<div class="input-group-sm position-rel">'
        +'<input type="text" name="question['+question+'][title]" class="form-control" placeholder="问题(备选)">'
        +'<a href="javascript:void(0)" class="question-close"><i class="icon-close10"></i></a>'
        +'</div>'
        +'</div>'

        +'<div class="form-group bdb-grey form-vb-cell">'
        +'<span>是否多选</span>'
        +'<div class="pull-right fs12">'
        +'<div class="ui-switch pull-right">'
        +'<input type="checkbox" name="question['+question+'][ismanyasr]" value="1" checked="checked">'
        +'</div>'
        +'</div>'
        +'</div>'

        +'<div class="vote-option">'
        +'<div class="form-group bdb-grey">'
        +'<div class="input-group-sm position-rel">'
        +'<input type="text" name="question['+question+'][answer][]" class="form-control" placeholder="选项一">'
        +'</div>'
        +'</div>'

        +'<div class="form-group bdb-grey">'
        +'<div class="input-group-sm position-rel">'
        +'<input type="text" name="question['+question+'][answer][]" class="form-control" placeholder="选项二">'
        +'</div>'
        +'</div>'

        +'</div>'

        +'<p class="m-b-0">'
        +'<a href="javascript:void(0)" class="btn btn-block option-add text-gray2 m-t-10 m-b-10" question-id="'+question+'">添加选项</a>'
        +'</p>'
        +'</div>'

        );//before
        question++;
        /*i+=2;*/
        $(".question-close").click(function(){
            $(this).closest(".bg-white").remove();
        });
    });

    /* 回复评论点击获取用户名到文本框 */
    $(".reply-name").click(function(){
        var replyName = $(this).closest("p").find(".reply-user1").text();
        var replyUser = $(".reply-user");
        replyUser.text("回复评论：");
        replyUser.text("回复 "+replyName+"：");
        var dataId = $(this).attr("data-id");
        $("#reply-id").val(dataId);
    });

    $(".btn-reply").click(function(){
        var replyUser = $(".reply-user");
        replyUser.text("回复评论：");
        $(".submit-reply").attr("replay-id","");
    });


    /* 汉堡点完变X */
    $(".navbar-toggler").click(function(){
        var aria = $(this).attr("aria-expanded");
        if(aria == 'true'){
            $(this).find(".burger").removeClass("icon-down");
            $(this).find(".burger").addClass("icon-01")
        }else {
            $(this).find(".burger").removeClass("icon-01");
            $(this).find(".burger").addClass("icon-down")
        }
    });

    /* 搜索结果页面选项卡切换红三角效果 */
    $(".search-result .nav-link").click(function(){
        $(".search-result .nav-link").find("i").removeClass("fa-caret-up");
        $(this).find("i").addClass("fa-caret-up");
    });

    $(document).on('click','do-like',function(){
        var id = $(this).attr('data-id');
        var _this = $(this);
        $.post($(this).attr('url'),{'id':id},function(){
            var icon = _this.find('icon-heart-o15');
            icon.removeClass('icon-heart-o15');
            icon.addClass('icon-heart');
        });
    });

    $('.do-like').click(function(){
        var id = $(this).attr('data-id');
        var _this = $(this);
        $.post($(this).attr('url'),{'id':id},function(data){
            if(data.status==1){
                var icon = _this.find('.icon-heart-o15');
                var com_like = _this.find('.com-like');
                icon.removeClass('icon-heart-o15');
                icon.addClass('icon-heart15');
                com_like.text(parseInt(com_like.text())+1);
            }
        });
    });
});

/* 创建表单校验 */
function voteCheck(){
    var returnFalse = true;
    var vote_check =  $(".vote-check");
    $(vote_check).each(function(k,v){
        if($(v).val() == ''){
            console.log(vote_check[0]);
            vote_check[k].focus();
            returnFalse = false;
            return false;
        }
    });
    return returnFalse;
}


function checkPhone(obj){
    var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
    var phone = $("#votePhone").val();
    if(!myreg.test(phone))
    {
        $('#msg').html('请输入有效的手机号码！');
        return false;
    }
}

var countdown=90;
function settime(obj) {
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.innerText="重新获取验证码";
        countdown = 90;
        return;
    } else {
        obj.setAttribute("disabled", true);
        obj.innerText ="重新发送(" + countdown + ")";
        countdown--;
    }
    setTimeout(function() {settime(obj)},1000)
}

function subVerify(evt){
    var phone = $('#votePhone').val();
    var verify = $('#verify').val();
    $.post('/user/center/checkVerify',{'phone':phone,'verify':verify},function(data){
        if(data==10001){
            window.location.href="/user/center/myuserinfo.html";
        }else {
            $('#msg2').html('验证码错误！');
        }
    });
}

function mvcheck(){
    var result = true;
    var key = '';
    $(".form").each(function(k,v){
        var flag = false;
        var inputs = $(v).find('input');
        inputs.each(function(kk,vv){
            if($(vv).prop('checked')){
                flag = true;
            }
        });
        if(!flag){
            result = false;
            key = k;
            return false;
        }else{
            $('#div'+k).css('border','');
        }
    });
    if(!result){
        $('.form').css('border','1px solid #F00');
        $("html,body").animate({scrollTop:$('.form').offset().top-50},500);
    }
    return result;
}

/* 注册登录显示密码 */
$(".check-password").click(function () {
    var inputPassword = $(".input-password");
    if(inputPassword.attr("type") == 'text' ){
        $(this).css("background-image","url('/static/mobile/images/yanjing68.png')");
        inputPassword.attr("type","password")
    }else {
        $(this).css("background-image","url('/static/mobile/images/yanjingkan68.png')");
        inputPassword.attr("type","text")
    }
});

/* 协议打勾 */
$(".deal").click(function () {
    $(this).toggleClass("hongdian");
    if($(this).is(".hongdian")){
        $("button[type=submit]").attr("disabled",false)
    }else {
        $("button[type=submit]").attr("disabled",true)
    }
})

$('.input-password').focus(function(){
    $('.massage').text('');
});


function getBindVerifyCode(obj){
    $.post('getBindVerifyCode',{'username':$('#mobile').val()},function () {settime(obj)});
}

function getForgetpwdVerify(obj){
    $.post('/home/login/getForgetpwdVerify',{'username':$('#username').val()},function () {
        settime(obj)
    });
}

function getVerifyCode(obj){
    $.post('/home/login/getVerifyCode',{'username':$('#username').val()},function () {
        settime(obj)
    });
}

var countdown=90;
function settime(obj) {
    if (countdown == 0) {
        obj.removeAttribute("disabled");
        obj.innerText="重新获取验证码";
        countdown = 90;
        return;
    } else {
        obj.setAttribute("disabled", true);
        obj.innerText ="重新发送(" + countdown + ")";
        countdown--;
    }
    setTimeout(function() {settime(obj)},1000)
}

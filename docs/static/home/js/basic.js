/**
 * Created by Dennis on 2016/3/10.
 */
$(function(){

    $('.ajaxForm').ajaxForm({
        success: function(data){
            if (data.status == 1) {
                $('.massage').text(data.info);
                window.location.href=data.url;
            } else {
                $('.massage').text(data.info);
            }
        },
        dataType: 'json'
    });

    /*注册、登录、忘记密码*/

    $(".btn-loginwin").click(function () {
        $(".windows").hide();
        $(".login-win").show();
        $(".login-title").html("登录");
    })

    $(".btn-register").click(function () {
        $(".windows").hide();
        $(".register-win").show();
        $(".login-title").html("注册");
    });

    $('.input-tel').focus(function(){
        $('.massage').text('');
    });

    $('.input-password').focus(function(){
        $('.massage').text('');
    })

    $(".btn-login1").click(function () {
        $(this).text('登录中...');
        $('.ajaxForm_1').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $('.massage').text(data.info);
                    window.location.href=data.url;
                } else {
                    $('.massage').text(data.info);
                    $('.btn-login1').text('登录');
                }
            },
            dataType: 'json'
        });
    });

    $(".btn-register1").click(function () {
        $('.ajaxForm_2').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $(".windows").hide();
                    $(".register2-win").show();
                    $('#registr_hide_username').val($('#registr_show_username').val());
                    $('.massage').text('');
                } else {
                    $('.massage').text(data.info);
                }
            },
            dataType: 'json'
        });
    });

    $(".btn-register2").click(function () {
        $('.ajaxForm_3').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $('.massage').text('');
                    window.location.href=data.url;
                } else {
                    $('.massage').text(data.info);
                }
            },
            dataType: 'json'
        });
    });

    $(".btn-forget").click(function () {
        $(".windows").hide();
        $(".forget-win").show();
        $(".login-title").html("忘记密码");
    });

    $(".btn-forget1").click(function () {
        $('.ajaxForm_4').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $(".windows").hide();
                    $(".forget2-win").show();
                    $('.massage').text('');
                    $('#forget_hide_username').val($('#forget_show_username').val());
                } else {
                    $('.massage').text(data.info);
                }
            },
            dataType: 'json'
        });
    });

    $(".btn-forget2").click(function () {
        $('.ajaxForm_5').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $(".windows").hide();
                    $(".forget3-win").show();
                    $('.massage').text('');
                    $('#change_hide_username').val($('#forget_show_username').val());
                    $('#change_hide_verify').val($('#forget_show_verify').val());
                } else {
                    $('.massage').text(data.info);
                }
            },
            dataType: 'json'
        });
    });

    $(".btn-forget3").click(function () {
        $('.ajaxForm_6').ajaxForm({
            success: function(data){
                if (data.status == 1) {
                    $('.massage').text('');
                    $('.windows').hide();
                    $(".login-win").show();
                } else {
                    $('.massage').text(data.info);
                }
            },
            dataType: 'json'
        });
    });

    /* 协议打勾 */
    $(".deal").click(function () {
        console.log("222");
        $(this).toggleClass("hongdian");
        if($(this).is(".hongdian")){
            $("button[type=submit]").attr("disabled",false)
        }else {
            $("button[type=submit]").attr("disabled",true)
        }
    });


    /*创建投票点击展开箭头旋转*/
    var circle = 1;
    $(".vote-more").click(function(){
        if(circle == 1){
            $(this).find(".fa").css({
                "-webkit-transform":"rotate(180deg) ",
                "transform":"rotate(180deg)"
            });
            circle = 0;
        }else{
            $(this).find(".fa").css({
                "-webkit-transform":"rotate(0deg) ",
                "transform":"rotate(0deg)"
            });
            circle = 1;
        }
    });

    $(document).on('click','do-like',function(){
        var id = $(this).attr('data-id');
        var _this = $(this);
        $.post($(this).attr('url'),{'id':id},function(){
            var icon = _this.find('.fa-heart-o');
            icon.removeClass('fa-heart-o');
            icon.addClass('fa-heart');
        });
    });

    $('.do-like').click(function(){
        var id = $(this).attr('data-id');
        var _this = $(this);
        $.post($(this).attr('url'),{'id':id},function(data){
            if(data.status==1){
                var icon = _this.find('.fa-heart-o');
                var com_like = _this.find('.com-like');
                icon.removeClass('fa-heart-o');
                icon.addClass('fa-heart');
                com_like.text(parseInt(com_like.text())+1);
            }
        });
    });


    /* 创建投票增加、删除选项功能 */

    var i = 3;
    var question = 3;
    $(document).on('click','.option-add-dan',function(){
        $(this).closest(".form-group").before(
            '<div class="form-group row">'
            +'<label for="" class="col-sm-2 form-control-label">选项</label>'
            +'<div class="col-sm-10">'
            +'<input type="text" name="vote[answer][]" class="form-control form-control-sm" id="" placeholder="请输入选项内容">'
            +'<a href="javascript:void(0)" class="close-input"><i class="fa fa-close"></i></a>'
            +'</div>'
            +'</div>'
        );
        i++;
        $(".close-input").click(function(){
            $(this).closest(".form-group").remove();
        });
    });

    $(document).on('click','.option-add',function(){
        var questionId = $(this).attr("question-id");
        $(this).closest(".form-group").before(
        '<div class="form-group row">'
        +'<label for="" class="col-sm-2 form-control-label">选项</label>'
        +'<div class="col-sm-10">'
        +'<input type="text" name="vote[question]['+questionId+'][answer][]" class="form-control form-control-sm" id="" placeholder="请输入选项内容">'
        +'<a href="javascript:void(0)" class="close-input"><i class="fa fa-close"></i></a>'
        +'</div>'
        +'</div>'
        );
        i++;
        $(".close-input").click(function(){
            $(this).closest(".form-group").remove();
        });
    });

    $(".add-question").click(function(){
        $(this).closest(".form-group").before(

        '<div>'
        +'<div class="form-group row">'
        +'<label for="voteQuestion" class="col-sm-2 form-control-label">问题</label>'
        +'<div class="col-sm-10">'
        +'<input type="text" name="vote[question]['+question+'][title]" class="form-control form-control-sm" id="voteQuestion" placeholder="请输入问题">'
        +'</div>'
        +'</div>'

        +'<div class="form-group row">'
        +'<label class="col-sm-2">单选/多选</label>'
        +'<div class="col-sm-10">'
        +'<label class="radio-inline c-input c-radio">'
        +'<input type="radio" name="vote[question]['+question+'][ismany]" value="0" checked>'
        +'<span class="c-indicator"></span>'
        +'单选'
        +'</label>'
        +'<label class="radio-inline c-input c-radio">'
        +'<input type="radio" name="vote[question]['+question+'][ismany]" value="1">'
        +'<span class="c-indicator"></span>'
        +'多选'
        +'</label>'
        +'</div>'
        +'</div>'

        +'<div class="form-group row">'
        +'<label for="" class="col-sm-2 form-control-label">选项</label>'
        +'<div class="col-sm-10">'
        +'<input type="text" name="vote[question]['+question+'][answer][]" class="form-control form-control-sm" id="" placeholder="请输入选项内容">'
        +'</div>'
        +'</div>'
        +'<div class="form-group row">'
        +'<label for="" class="col-sm-2 form-control-label">选项</label>'
        +'<div class="col-sm-10">'
        +'<input type="text" name="vote[question]['+question+'][answer][]" class="form-control form-control-sm" id="" placeholder="请输入选项内容">'
        +'</div>'
        +'</div>'

        +'<div class="form-group row">'
        +'<label for="" class="col-sm-2 form-control-label"></label>'
        +'<div class="col-sm-10">'
        +'<a href="javascript:void(0)" class="btn btn-secondary btn-sm option-add" question-id="'+question+'">增加选项</a>'
        +'</div>'
        +'</div>'

        +'</div>'
        +'<hr>'


        );//before

        question++;
        i+=2;
        $(".question-close").click(function(){
            $(this).closest(".bg-white").remove();
        });
    });

    $(".vote-checkbox").click(function(){
        $(this).closest(".question").prev(".answer").click();
        $(this).closest(".question").toggleClass("active");
        $(this).find(".answer-checked").toggleClass("collapse");
    });
    $(".vote-radio").click(function(){
        $(this).closest(".question").prev(".answer").click();
        $(this).closest(".row").find(".question").removeClass("active");
        $(this).closest(".question").addClass("active");
        $(this).closest(".row").find(".answer-checked").addClass("collapse");
        $(this).find(".answer-checked").removeClass("collapse");
    });
    function checkcontent(){
        if(!$('#com_content').val()){
            $('#submit-info').text('内容不能为空！');
            return false;
        }
    }


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

function getForgetVerifyCode(obj){
    $.post('/home/login/getForgetpwdVerify',{'username':$('#forget_hide_username').val()},function () {settime(obj)});
}

function getVerifyCode(obj){
    $.post('/home/login/getVerifyCode',{'username':$('#registr_hide_username').val()},function () {
        settime(obj)
    });
}

function getBindVerifyCode(obj){
    $.post('getBindVerifyCode',{'username':$('#username').val()},function () {settime(obj)});
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




$(function(){
    var picpath = "http://47.94.199.232/data/upload/";
    var _js_ajax_submit_input=$('.js-ajax-submit-input');
    _js_ajax_submit_input.on('click',function(e){
        e.preventDefault();
        var _this = $(this);
        var action = $('#form').attr('action');
        var data = $(this).closest("p").prev(".com_content").val();
        var post_id = _this.attr('data-id');
        $.post(action,{'com_content':data,'post_id':post_id},function(data){
            location.reload();
        });
    });

    //评论回复
    var _js_ajax_submit_replay=$('.js-ajax-submit-replay');
    $(document).on('click','.js-ajax-submit-replay',function(e){
        e.preventDefault();
        var _this = $(this);
        var action = "/portal/comment/replayPost";
        var data = $(this).closest("span").prev("input").val();
        var post_id = _this.attr('data-id');
        var replay_id = _this.attr('replay-id');
        var com_id = _this.attr('com-id');
        $.post(action,{'com_content':data,'post_id':post_id,'replay_id':replay_id,'com_id':com_id},function(data){
            location.reload();
        });
    });

    //评论回复
    var _js_ajax_submit_replay2=$('.js-ajax-submit-replay2');
    $(document).on('click','.js-ajax-submit-replay2',function(e){
        e.preventDefault();
        var _this = $(this);
        var action = "/vote/comment/replayPost";
        var data = $(this).closest("span").prev("input").val();
        var post_id = _this.attr('data-id');
        var replay_id = _this.attr('replay-id');
        var com_id = _this.attr('com-id');
        $.post(action,{'com_content':data,'post_id':post_id,'replay_id':replay_id,'com_id':com_id},function(data){
            location.reload();
        });
    });

    var _js_count_btn=$('.js-count-btn');
    _js_count_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var href = _this.attr('href');
        if(href == 'javascript:;'){
            return false;
        }
        var _num = _this.children('.post-like').text();
        _this.children('.post-like').text(Number(_num)+1);
        _this.children('.fa').removeClass('fa-heart-o');
        _this.children('.fa').addClass('fa-heart');
        _this.attr('href','javascript:;');
        $.post(href,{},function(data){},"json");
    });
    //收藏
    var _js_favorite_btn=$('.js-favorite-btn');
    _js_favorite_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var href = _this.attr('href');
        $.post(href,{},function(data){
            if (data.status === 1) {
                _this.children().removeClass('fa-star-o');
                _this.children().addClass('fa-star');
            } else if (data.status === 0) {
                _this.children().removeClass('fa-star');
                _this.children().addClass('fa-star-o');
            }
        },"json");
    });
    //文章页投票提交
    $(".btn-submit").click(function(){
        var vote = [];
        var vote_id = $(this).attr('vote-id');
        $(".question.active").prev("label").find("input").each(function(k,v){
            vote[k] = $(this).val();
        });
        url = "/Portal/article/voting";
        $.post(url,{'vote':vote,'vote_id':vote_id},function(data){
            window.location.reload();
        });
    });
     //搜索榜单加载更多
    var js_more_posts_page = 2;
    var _js_more_posts_btn = $('.js-more-posts');
    _js_more_posts_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var href = _this.attr('href');
        if(href == 'javascript:;'){
            return false;
        }
        $.post(href,{'page':js_more_posts_page},function(data){
            if (data.state === 'success') {
                var html = '';
                $(data.posts).each(function(k,v){
                    html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+picpath+v.smeta+');"><a target="_blank" href="/articles/'+v.tid+'.html" class="permacover"></a><span class="object-header-meta"><a href="javascript:void(0)"><span class="">'+v.post_modified+'</span><span class="right"><span class="read-time"><i class="fa fa-heart-o"></i> '+v.post_like+'</span>&nbsp;&nbsp;<span class="read-time"><i class="fa fa-commenting-o"></i> '+v.comment_count+'</span></span></a></span></div><div class="object-meta"><h3><a href="/articles/'+v.tid+'.html">'+v.post_title+'</a></h3><div class="object-excerpt"><p>'+v.post_mime_type+'</p></div></div></div></div>';
                });
                $('.posts-list').append(html);
                //数据处理
                js_more_posts_page++;
            } else if (data.state === 'fail') {
                _this.text('没有更多');
                _this.attr('href','javascript:;');
            }
        },"json");
    });


  //搜索投票加载更多
    var js_more_votes_page = 2;
    var _js_more_votes_btn = $('.js-more-votes');
    _js_more_votes_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var href = _this.attr('href');
        if(href == 'javascript:;'){
            return false;
        }
        $.post(href,{'page':js_more_votes_page},function(data){
            if (data.state === 'success') {
                var html = '';
                $(data.votes).each(function(k,v){
                    html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+v.smeta+');"><a target="_blank" href="/votesw/'+v.id+'.html" class="permacover"></a><span class="object-header-meta"><a href="javascript:void(0)"><span class="">'+v.createtime+'</span><span class="right"><span class="read-time"><i class="fa fa-eyes"></i> '+v.hits+'</span></span></a></span></div><div class="object-meta"><h3><a href="/votesw/'+v.id+'.html">'+v.title+'</a></h3><div class="object-excerpt"><p>'+v.description+'</p></div></div></div></div>';
                });
                $('.vote-list').append(html);
                //数据处理
                js_more_votes_page++;
            } else if (data.state === 'fail') {
                _this.text('没有更多');
                _this.attr('href','javascript:;');
            }
        },"json");
    });
    
    $('.reply-load').click(function(){
        var url = "/portal/comment/ajaxLoadRply";
        var id = $(this).attr('data-id');
        var cid = $(this).attr('replay-id');
        var page = 1;
        $.post(url,{'id':id,'cid':cid,'page':page},function(data){
            var html = "";
            var del_html = "";
            $(data.replays).each(function(k,v){
                html +='<div><p class="media-text"><span class="text-gray">'+v.full_name+'</span>';
                if(v.uid == data.del_status && data.del_status){
                    del_html = '<a href="javascript:void(0)" class="text-danger">撤回</a>&nbsp;&nbsp;';
                }
                if(v.toname){
                    html += ' 回复 <span class="text-gray">'+v.toname+'：</span>';
                }else{
                    html +="：";
                }
                html += ''+v.content+'</p><p class="help-block text-right"><span class="left">'+v.createtime+'</span>'+del_html+'<a href="javascript:void(0)" class="text-gray btn-coco"> 回复 </a>&nbsp;&nbsp;<a href="/portal/comment/do_like/id/'+v.id+'" class="text-gray js-count-btn"><i class="fa fa-heart-o"></i> <span class="post-like">'+v.com_like+'</span></a>&nbsp;&nbsp;</p><div class="input-group input-group-sm m-y collapse"><input type="text" class="form-control com-content" placeholder="回复内容"><span class="input-group-btn"><button class="btn btn-danger js-ajax-submit-replay" data-id="'+id+'" replay-id="'+v.id+'" com-id="'+cid+'" type="submit">回复</button></span></div></div><hr class="m-t-0">';
            });
            $('.reply-list').html(html);
        });
    });    
    
    $('.reply-load2').click(function(){
        var url = "/vote/comment/ajaxLoadRply";
        var id = $(this).attr('data-id');
        var cid = $(this).attr('replay-id');
        var page = 1;
        $.post(url,{'id':id,'cid':cid,'page':page},function(data){
            var html = "";
            var del_html = "";
            $(data.replays).each(function(k,v){
                html +='<div><p class="media-text"><span class="text-gray">'+v.full_name+'</span>';
                if(v.uid == data.del_status && data.del_status){
                    del_html = '<a href="javascript:void(0)" class="text-danger">撤回</a>&nbsp;&nbsp;';
                }
                if(v.toname){
                    html += ' 回复 <span class="text-gray">'+v.toname+'：</span>';
                }else{
                    html +="：";
                }
                html += ''+v.content+'</p><p class="help-block text-right"><span class="left">'+v.createtime+'</span>'+del_html+'<a href="javascript:void(0)" class="text-gray btn-coco"> 回复 </a>&nbsp;&nbsp;<a href="/portal/comment/do_like/id/'+v.id+'" class="text-gray js-count-btn"><i class="fa fa-heart-o"></i> <span class="post-like">'+v.com_like+'</span></a>&nbsp;&nbsp;</p><div class="input-group input-group-sm m-y collapse"><input type="text" class="form-control com-content" placeholder="回复内容"><span class="input-group-btn"><button class="btn btn-danger js-ajax-submit-replay" data-id="'+id+'" replay-id="'+v.id+'" com-id="'+cid+'" type="submit">回复</button></span></div></div><hr class="m-t-0">';
            });
            $('.reply-list2').html(html);
        });
    });    
});

function words_deal()
{
    var curLength=$(".comment-text").val().length;
    if(curLength>150)
    {
        var num=$(".comment-text").val().substr(0,150);
        $(".comment-text").val(num);
        $(".textcontent").css("color","#b81c22");
    }
    else
    {
        $(".textcontent").text(150-$(".comment-text").val().length);
    }
}

function checkPhone(obj){
    var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
    var phone = $("#votePhone").val();
    if(!myreg.test(phone))
    {
        $('#msg').html('请输入有效的手机号码');
        return false;
    }else {
        $.post('/user/center/checkPhone',{'phone':phone},function(data){
            if(data==0){
                $('#msg').html('');
                settime(obj);
                $.post('/user/center/sendVerify',{'phone':phone},function(data){});
            }else{
                $('#msg').html('手机号码已被绑定');
            }
        });
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
        if(data==10001){window.location.href="/user/center/userbinphone.html";}
    });
}

/* 多问题投票校验 */

function mvcheck(){
    var result = true;
    var key = '';
    $(".vote-question").each(function(k,v){
        var flag = false;
        var inputs = $(v).find('input');
        console.log(inputs);
        inputs.each(function(kk,vv){
            console.log(vv);
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
        $('#div'+key).css('border','1px solid #F00');
        $("html,body").animate({scrollTop:$('#div'+key).offset().top-50},500)
    }
    return result;
}



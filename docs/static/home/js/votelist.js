$(function(){
    var picpath = "http://47.94.199.232/data/upload/";
    var _js_more_vote_page = 2 ;
    var stop=true;
    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight){
            if(stop==true){
                stop=false;
                var href = '/list/loadmorevote';
                $.post(href,{'page':_js_more_vote_page},function(data){
                    if (data.state === 'success') {
                        var html = '';
                        $(data.votes).each(function(k,v){
                        	html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+v.smeta+');"><a target="_blank" href="/votesw/'+v.id+'.html" class="permacover"></a><span class="object-header-meta"><a href="javascript:void(0)"><span class="">'+v.createtime+'</span><span class="right"><span class="read-time"><i class="fa fa-eye"></i> '+v.hits+'</span></span></a></span></div><div class="object-meta"><div class="object-meta-sub top"><span class="left"><span class="meta-tags">#<a href="javascript:void(0)">'+v.keywords+'</a></span></span></div><h6><a href="/votesw/'+v.id+'.html">'+v.title+'</a></h6></div></div></div>';
                        });
                        $('.vote-list').append(html);
                        stop=true;
                        //数据处理
                        _js_more_vote_page++;
                    }
                },"json");
            }
        }
    });
});




    
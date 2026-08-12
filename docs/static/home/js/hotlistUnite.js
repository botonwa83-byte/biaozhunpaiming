$(function(){
    var picpath = "http://47.94.199.232/data/upload/";
    var stop=true;
    var term_id = $('.hotunite-list').attr('term-id');
    var _js_more_hotunite_page = 2 ;
    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight){
            if(stop==true){
                stop=false;
                var href = '/list/loadmorehotunite';
                $.post(href,{'page':_js_more_hotunite_page,'term_id':term_id},function(data){
                    if (data.state === 'success') {
                        var html = '';
                        $(data.articles).each(function(k,v){
                        	html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+picpath+v.smeta+');"><a target="_blank" href="/articles/'+v.tid+'.html" class="permacover"></a><span class="object-header-meta"><a href="/articles/'+v.tid+'.html"><span class="">'+v.post_modified+'</span><span class="right"><span class="read-time"><i class="fa fa-heart-o"></i> '+v.post_like+'</span>&nbsp;&nbsp;<span class="read-time"><i class="fa fa-commenting-o"></i> '+v.comment_count+'</span></span></a></span></div><div class="object-meta"><h3><a href="/articles/'+v.tid+'.html">'+v.post_title+'</a></h3><div class="object-excerpt"><p>'+v.post_mime_type+'</p></div></div></div></div>';
                        })
                        $('.hotunite-list').append(html);
                        stop=true;
                        //数据处理
                        _js_more_hotunite_page++;
                    }
                },"json");
            }
        }
    });
});

    
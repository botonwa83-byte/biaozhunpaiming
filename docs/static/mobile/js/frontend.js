$(function(){
    var _js_more_page = 2 ;
    var stop=true;
    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight + 500){
            if(stop == true){
                stop = false;
                var href = $('.load-more').attr('url');
                alert(href);
                $('.load-more').text('加载中...');
                $.post(href,{'page':_js_more_page,'ad':false},function(data){
                    if (data.code === 200) {
                        $('.load-more').prev('div').append(data.html);
                        _js_more_page++;
                    } else if (data.code === 400) {
                        $('.load-more').text('没有更多了');
                        $('.load-more').attr('url','');
                    }
                },"json");
            }
        }
    });
});
html += '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+picpath+v.smeta+');"><a target="_blank" href="/articles/'+v.tid+'.html" class="permacover"></a><span class="object-header-meta"><a href="/articles/'+v.tid+'.html"><span class="">'+v.post_modified+'</span><span class="right"><span class="read-time"><i class="fa fa-heart-o"></i> '+v.post_like+'</span>&nbsp;&nbsp;<span class="read-time"><i class="fa fa-commenting-o"></i> '+v.comment_count+'</span></span></a></span></div><div class="object-meta"><h3><a href="/articles/'+v.tid+'.html">'+v.post_title+'</a></h3><div class="object-excerpt"><p>'+v.post_mime_type+'</p></div></div></div></div>';

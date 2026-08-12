$(function(){
    var picpath = "http://47.94.199.232/data/upload/";
    var _js_more_hot_page = 2 ;
    var stop=true;
    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight){
            if(stop==true){
                stop=false;
                var href = '/list/loadmorehot';
                $.post(href,{'page':_js_more_hot_page},function(data){
                    if (data.state === 'success') {
                        var html = '';
                        $(data.objects).each(function(k,v){
                        	html += '<div class="col-xs-12"><div class="object collection"><div class="object-header image full" style="background-image:url('+v.smeta+');"><a href="/hotlistUnite/'+v['term_id']+'.html" class="permacover"></a><div class="object-header-content"><h3><a href="/hotlistUnite/'+v['term_id']+'.html">'+v.name+'</a></h3><div class="object-header-content-excerpt"><p>'+v.description+'</p></div></div></div></div></div>';
                        })
                        $('.content-hotlist').append(html);
                        stop=true;
                        //数据处理
                        _js_more_hot_page++;
                    } else if (data.state === 'fail') {

                    }
                },"json");
            }
        }
    });
});

    
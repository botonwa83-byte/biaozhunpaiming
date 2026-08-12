$(function(){
    var _js_more_page = 2 ;
    var stop=true;
    $(window).scroll(function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if($(document).height() <= totalheight + 50){
            if(stop == true){
                stop = false;
                var href = $('.load-more').attr('url');
                $('.load-more').text('加载中...');
                $.post(href,{'page':_js_more_page,'ad':false},function(data){
                    if (data.code == 200) {
                        var html = '';
                        $(data.data).each(function(k,v){
                            if(data.type == 'articles'){
                                html += '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+v.smeta+');"><a target="_blank" href="/articles/'+v.tid+'.html" class="permacover"></a><span class="object-header-meta"><a href="/articles/'+v.tid+'.html"><span class="">'+v.post_modified+'</span><span class="right"><span class="read-time"><i class="fa fa-heart-o"></i> '+v.post_like+'</span>&nbsp;&nbsp;<span class="read-time"><i class="fa fa-commenting-o"></i> '+v.comment_count+'</span></span></a></span></div><div class="object-meta"><h3><a href="/articles/'+v.tid+'.html">'+v.post_title+'</a></h3><div class="object-excerpt"><p>'+v.post_mime_type+'</p></div></div></div></div>';
                            }else if(data.type == 'terms'){
                                html += '<div class="col-xs-12 m-b"><div class="object collection"><div class="object-header image full" style="background-image:url('+v.smeta+');"><a href="/hotlistUnite/'+v['term_id']+'.html" class="permacover"></a><div class="object-header-content"><h3><a href="/hotlistUnite/'+v['term_id']+'.html">'+v.name+'</a></h3><div class="object-header-content-excerpt"><p>'+v.description+'</p></div></div></div></div></div>';
                            }else if(data.type == 'votes'){
                                html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+v.smeta+');"><a target="_blank" href="/votesw/'+v.id+'.html" class="permacover"></a><span class="object-header-meta"><a href="javascript:void(0)"><span class="">'+v.createtime+'</span><span class="right"><span class="read-time"><i class="fa fa-eye"></i> '+v.hits+'</span></span></a></span></div><div class="object-meta"><div class="object-meta-sub top"><span class="left"><span class="meta-tags">#<a href="javascript:void(0)">'+v.keywords+'</a></span></span></div><h6><a href="/votesw/'+v.id+'.html">'+v.title+'</a></h6></div></div></div>';
                            }else if(data.type == 'user_votes'){
                                var status_html = '';
                                if(v.vote_status == 1){
                                    status_html = '<span class="text-green">已审核</span>';
                                }else{
                                    status_html = '<span class="text-red">未审核</span>';
                                }
                                html +='<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4"><div class="object post"><div class="object-header image" style="background-image:url('+v.smeta+');"><a target="_blank" href="/votesw/'+v.id+'.html" class="permacover"></a><span class="object-header-meta"><a href="javascript:void(0)"><span class="">'+v.createtime+'</span><span class="right">'+status_html+'<span class="read-time"><i class="fa fa-eye"></i> '+v.hits+'</span></span></a></span></div><div class="object-meta"><div class="object-meta-sub top"><span class="left"><span class="meta-tags">#<a href="javascript:void(0)">'+v.keywords+'</a></span></span></div><h6><a href="/votesw/'+v.id+'.html">'+v.title+'</a></h6></div></div></div>';
                            }
                        });
                        $('.load-more').prev('div').append(html);
                        stop=true;
                        _js_more_page++;
                    }else{
                        $('.load-more').text('没有更多了');
                        $('.load-more').attr('url','');
                    }
                },"json");
            }
        }
    });
});

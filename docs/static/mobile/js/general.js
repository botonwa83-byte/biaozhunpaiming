$(function(){
	//加载更多
    var _js_more_page = 2 ;
    var _js_more_btn = $('.js-more-btn');
    _js_more_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('url');
        if(url == ''){
            return false;
        }else{
            _this.text('加载中...');
        }
        $.post(url,{'page':_js_more_page},function(data){
            if (data.code === 200) {
                _this.closest('.container').prev('div').append(data.html);
                $("img.lazy_"+_js_more_page).lazyload({threshold:0,effect:"fadeIn",failurelimit:2,skip_invisible:false});
                _js_more_page++;
                _this.text('加载更多');
            } else if (data.code === 400) {
                _this.text('没有更多');
                _this.attr('url','');
            }
        },"json");
        
    });
    //加载更多
    var _order_js_more_page = 2 ;
    var _order_js_more_btn = $('.js-more-order-btn');
    _order_js_more_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('url');
        if(url == ''){
            return false;
        }else{
            _this.text('加载中...');
        }
        $.post(url,{'page':_order_js_more_page},function(data){
            if (data.code === 200) {
                $('.content-list').append(data.html);
                _order_js_more_page++;
                _this.text('加载更多');
            } else if (data.code === 400) {
                _this.text('没有更多');
                _this.attr('url','');
            }
        },"json");
        
    });
    //加载更多
    var _vote_js_more_page = 2 ;
    var _vote_js_more_btn = $('.js-more-vote-btn');
    _vote_js_more_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('url');
        if(url == ''){
            return false;
        }else{
            _this.text('加载中...');
        }
        $.post(url,{'page':_vote_js_more_page},function(data){
            if (data.code === 200) {
                $('.vote-list').append(data.html);
                _vote_js_more_page++;
                _this.text('加载更多');
            } else if (data.code === 400) {
                _this.text('没有更多');
                _this.attr('url','');
            }
        },"json");
        
    });
    //加载更多
    var _user_vote_js_more_page = 2 ;
    var _user_vote_js_more_btn = $('.js-user-vote-more-btn');
    _user_vote_js_more_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('url');
        if(url == ''){
            return false;
        }else{
            _this.text('加载中...');
        }
        $.post(url,{'page':_user_vote_js_more_page},function(data){
            if (data.code === 200) {
                $('.vote-list').append(data.html);
                _user_vote_js_more_page++;
                _this.text('加载更多');
            } else if (data.code === 400) {
                _this.text('没有更多');
                _this.attr('url','');
            }
        },"json");
        
    });
    //热榜加载更多
    var _hot_js_more_page = 2 ;
    var _js_hot_more_btn = $('.js-hot-more-btn');
    _js_hot_more_btn.on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var url = _this.attr('url');
        if(url == ''){
            return false;
        }else{
            _this.text('加载中...');
        }
        $.post(url,{'page':_hot_js_more_page},function(data){
            if (data.code == 200) {
                $('.content-hotlist').append(data.html);
                _hot_js_more_page++;
                _this.text('加载更多');
            } else if (data.code == 400) {
                _this.text('没有更多');
                _this.attr('url','');
            }
        },"json");
        
    });

    //赞，拍等，有数量操作的按钮
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
        _this.children('.fa').removeClass('icon-heart-o');
        _this.children('.fa').addClass('icon-heart');
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
                _this.children().removeClass('icon-star-o');
                _this.children().addClass('icon-star');
            } else if (data.status === 0) {
                _this.children().removeClass('icon-star');
                _this.children().addClass('icon-star-o');
            }
        },"json");
    });
});
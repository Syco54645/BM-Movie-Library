$(function() {
    $('.jq_hide').css({
        'opacity' : '.0'
    });
    $('#bg').css({
        'display' : 'none'
    });
    $('#vres').css({
        'opacity' : '.0'
    });
    $('#vtype').css({
        'opacity' : '.0'
    });
    $('#atype').css({
        'opacity' : '.0'
    });
    $('#achan').css({
        'opacity' : '.0'
    });
});

$(window).load(function() {

    /* Scrolling right pannel */
    var div_scroll = $('.scroll');
    var id = $('#bg').attr('alt');
    div_scroll.jScrollPane( {
        showArrows: true,
        animateScroll: true,
        stickToTop: true
    });
    if (id > 1 && ($('div#'+id).length)) {
        var api = div_scroll.data('jsp');
        api.scrollToElement('#'+id,true);
    }
    
    /* Load all panel in order */
    $('#bg').fadeIn('500', function() {
        $('.jq_hide').animate({
            'opacity' : '.9'
        }, 500, function() {
            $('#vres').animate({
                'opacity' : '1'
            }, 300, function() {
                $('#vtype').animate({
                    'opacity' : '1'
                }, 300, function() {
                    $('#atype').animate({
                        'opacity' : '1'
                    }, 300, function() {
                        $('#achan').animate({
                            'opacity' : '1'
                        }, 300);
                    });
                });
            });
        });
    });

    /* Animate buttons */
    $('.opacity').mouseenter(function(){
        $(this).animate({
            opacity: 0.5
        }, 200 );
    });
    $('.opacity').mouseleave(function(){
        $(this).animate({
            opacity: 1
        }, 200 );
    });
    
    /* Switching trailer */
    $('#trailer_play').click(function(){
        $('#trailer_thumb').fadeOut('slow');
        $('#trailer_play').fadeOut('slow');
        $('#trailer_layer').fadeIn('slow');
        $('#trailer').fadeIn('slow');
    });
    $('#trailer_layer').click(function(){
        $('#trailer').fadeOut('slow');
        $('#trailer_layer').fadeOut('slow');
        $('#trailer_play').fadeIn('slow');
        $('#trailer_thumb').fadeIn('slow');
    });
    
    /* Animate recently / random */
    $('.random_img, .recently_img').mouseover(function(){
        $(this).animate({
            height: '140px',
            width: '100px',
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });
    $('.random_img, .recently_img').mouseleave(function(){
        $(this).animate({
            height: '80px',
            width: '57px',
            opacity: '.7'
        }, {
            queue:false,
            duration:300
        });
    });
    
    /* Animate panel list / options */
    $('#panel_options, #panel_options_img').hover(function(){
        $('#panel_options').animate({
            right: '0px'
        }, {
            queue:false,
            duration:500
        });
        $('#panel_options_img').animate({
            right: '250px'
        }, {
            queue:false,
            duration:500
        });
        return false;
    }, function(){
        $('#panel_options').animate({
            right: '-250px'
        }, {
            queue:false,
            duration:500
        });
        $('#panel_options_img').animate({
            right: '0px'
        }, {
            queue:false,
            duration:500
        });
        return false;
    });
    $('#panel_list, #panel_list_img').hover(function(){
        $('#panel_list').animate({
            right: '0px'
        }, {
            queue:false,
            duration:500
        });
        $('#panel_list_img').animate({
            right: '250px'
        }, {
            queue:false,
            duration:500
        });
        return false;
    }, function(){
        $('#panel_list').animate({
            right: '-250px'
        }, {
            queue:false,
            duration:500
        });
        $('#panel_list_img').animate({
            right: '0px'
        }, {
            queue:false,
            duration:500
        });
        return false;
    });
});
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
    
    /* Switching right pannel list to options */
    $('#options').click(function(){
        $('#panel_list').fadeOut('slow', function() {
            $('#panel_options').fadeIn('slow');
        });
    });
    $('#back').click(function(){
        $('#panel_options').fadeOut('slow', function() {
            $('#panel_list').fadeIn('slow');
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
        $('#trailer').fadeIn('slow');
    });
    $('#bg').click(function(){
        $('#trailer').fadeOut('slow');
        $('#trailer_play').fadeIn('slow');
        $('#trailer_thumb').fadeIn('slow');
    });
});
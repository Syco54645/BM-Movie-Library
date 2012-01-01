$(window).load(function() {

    /* Load all panel in order */
    $('#bg').fadeIn(1000, function() {
        $('#panel_right').fadeIn(1000);
        $('#panel_bottom').fadeIn(1000, function() {
            $('#poster').fadeIn(500, function() {
                $('#vres').fadeIn(500, function() {
                    $('#vtype').fadeIn(500, function() {
                        $('#atype').fadeIn(500, function() {
                            $('#achan').fadeIn(500);
                        });
                    });
                });
            });
        });
        $('#info').fadeIn(1000);
        $('#panel_top').fadeIn(1000);
    
        /* Scrolling right pannel */
        var div_scroll = $('.scroll');
        var id = <?PHP echo $movie['id'] ?>;
        div_scroll.jScrollPane( {
            showArrows: true,
            animateScroll: true,
            stickToTop: true
        });
        if (id > 1) {
            var api = div_scroll.data('jsp');
            api.scrollToElement('#'+id,true);
        }
                    
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
    });
});
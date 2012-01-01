$(window).load(function() {
                
    /* Check/uncheck all */
    $('span.select').click(function() {
        $('input.check').attr('checked', true);
    });
    $('span.unselect').click(function() {
        $('input.check').removeAttr('checked', true);
    });
                
    /* Confirm */
    $(".p_confirm").click(function(){
        $("#p_confirm")
        .fadeIn()
        .html($(this).attr("title") + '<br/><br/><button id="yes" onClick=\"location.href=\'' + $(this).attr("href") + '\'\">' + $('#yes').html() + '</button> <button id="no"> ' + $('#no').html() + ' </button>');
        return false;
    });
    $("#p_confirm").click(function() {
        $(this).fadeOut();
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
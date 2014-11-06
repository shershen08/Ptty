
/* Not Demo Related stuff */

$(document).ready(function() {

    $("ul.dropdown li").hover(function(){
        $(this).addClass("hover");
        $('ul:first',this).css('visibility', 'visible');
    }, function(){
        $(this).removeClass("hover");
        $('ul:first',this).css('visibility', 'hidden');
    });
    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

    $('.modal-close, .modal-wrap').click(function(){
        $('.modal-wrap').hide(100);
    });

    $('.collapsed, .expanded').click(function(){
        if($(this).hasClass('collapsed')){
            $(this).removeClass('collapsed').addClass('expanded');
        }else{
            $(this).removeClass('expanded').addClass('collapsed');
        }
    });

    // Hide Header on on scroll down
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var navbarHeight = $('header').outerHeight();

    $(window).scroll(function(event){
        didScroll = true;
    });

    setInterval(function() {
        if (didScroll) {
            var st = $(this).scrollTop();
            if(Math.abs(lastScrollTop - st) <= delta){
                return;
            }
            if (st > lastScrollTop && st > navbarHeight){
                // Scroll Down
                $('header').removeClass('nav-down').addClass('nav-up');
            } else {
                // Scroll Up
                if(st + $(window).height() < $(document).height()) {
                    $('header').removeClass('nav-up').addClass('nav-down');
                }
            }
            lastScrollTop = st;
            didScroll = false;
        }
    }, 250);

});

function cmd_date(args){
    var now = new Date();
    var time = now + ' ';
    if(args.length > 1){
        args.shift();
        format = args.join(' ');
        var regex, time_obj = { 
            _d_ : now.getDate(), _m_ : now.getMonth()+1, _y_ : now.getFullYear(),
            _h_ : now.getHours(), _i_ : now.getMinutes(), _s_ : now.getSeconds() 
        };
        for (t in time_obj) {
            format = format.replace(new RegExp(t, "g"), time_obj[t]);
        };
        time = format;
    }
    return time;
}

function cmd_game(args, stat){
    var out = '';
    if(stat == 'start'){
        out = 'Hi! lets play rock-paper-scissors!</br>'
        +'(type <i>quit</i> or <i>exit</i> to end)</br>'
        +'Choose [r], [p] or [s].';
    }else if(stat == 'end'){
        out = 'Let\'s play again some time! Bye.'
    }else{
        rps = ['r','p','s'],
        choice = rps[Math.floor(Math.random() * rps.length)],
        win = null;
        if(choice === args[0]){
            win = 2;
        }else if(args[0] == 'r'){
            win = (choice == 'p') ? 1 : 0;
        }else if(args[0] == 'p'){
            win = (choice == 's') ? 1 : 0;
        }else if(args[0] == 's'){
            win = (choice == 'r') ? 1 : 0;
        }
        if(win !== null){
            if(win === 1){
                out = 'I win! ('+choice+') beats ('+args[0]+').';
            }else if(win === 0){
                out = 'You win. ('+args[0]+') beats ('+choice+').'
            }else{
                out = 'It\'s a tie!';
            }
            out = out.replace('(r)', 'rock').replace('(p)', 'paper')
            .replace('(s)', 'scissors')+'</br>';
        }
        out += 'Choose [r], [p] or [s].'; 
    }
    return out;
}
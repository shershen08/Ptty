<?php

if(isset($_POST['cmd'])){
    $in = explode(' ', strip_tags($_POST['cmd']));
    $out = array('type' => 'print', 'out' => '' );
    if(isset($in[0])){
        if($in[0] == 'ip'){
            # Do IP demo
            $ip = $_SERVER['REMOTE_ADDR'];
            if(isset($in[1])){
                if($in[1] == '-v6'){
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === true) {
                        if (strpos($ip, '.') > 0) {
                            $ip = substr($ip, strrpos($ip, ':')+1);
                        } else { //native ipv6
                            $out['out'] = $ip;
                        }
                    }else{
                        $is_v4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                        if (!$is_v4) {
                            $out['out'] = 'Not a valid IP.';
                        }else{
                            $iparr = array_pad(explode('.', $ip), 4, 0);
                            $Part7 = base_convert(($iparr[0] * 256) + $iparr[1], 10, 16);
                            $Part8 = base_convert(($iparr[2] * 256) + $iparr[3], 10, 16);
                            $out['out'] = '::ffff:'.$Part7.':'.$Part8;
                        }    
                    }
                }else{
                    $out['out'] = 'Wrong option. Use <i>ip -v6</i>.';
                }
            }else{
                $out['out'] = $ip;
            }
        }else if($in[0] == ''){
            $out['out'] = 'Empty.';
        }
    }

    # Output
    header('Content-Type: application/json');
    die(json_encode($out));
}

# Get full url for kty demos demos
$schema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$url = $schema.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);

# Get kty select options and random kitty ID.
$kitty_ids = array();
foreach (range(1, 10) as $num) {
    $kitty_id = str_pad($num, 2, '0', STR_PAD_LEFT);
    $kitty_file = 'img/kitties/'.$kitty_id.'.png';
    if(is_file($kitty_file)){
        $kitty_ids[] = $kitty_id;
    }
}
shuffle($kitty_ids);
$random_kitty = $kitty_ids[0];


# Set track me not cookie
if(isset($_GET['trackmenot']) || isset($_COOKIE['trackmenot'])){
    $expires = time() + (3600 * 24 * 30 * 6); // 6 months, take or give.
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
    setcookie('trackmenot', 'trackmenot', $expires, '/', $_SERVER['HTTP_HOST'], $secure, true);    
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demo - Ptty terminal emulator</title>
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="v/latest/css/Ptty.css" />
    <link rel="stylesheet" type="text/css" href="css/themes.css" />
    <style type="text/css">
        html, body       { height: 100%; width: 100%; margin: 0; padding: 0; }
        a                { text-decoration: none; color: #fcf; }
        a[href^='./']         { font-weight: bold; }
        a[href^='#']:before   { content: "#"; }
        a[href^='.']:after    { content: "⤢"; }
        a[href^='http']:after { content: "↗"; }
        a[download]:after     { content: "↓"; }

        .modal-wrap      { display:none; z-index:40001; }
        .modal-overlay   { position:fixed; z-index:40001; width:100%; height:100%; background-color:#000; opacity: 0.5; -ms-filter:"alpha(Opacity=50)";filter:alpha(opacity=50); }
        .modal-v-offset  { position:fixed; top:30%; width:100%; z-index:40002; }
        .modal-window    { width:405px; position:relative; margin:0 auto; background-color:#000; border-radius:4px; padding:10px; border:1px solid #ddd; }
    </style>
</head>
<body>
    <!-- Modal dialog -->
    <aside class="modal-wrap">
        <div class="modal-overlay">&nbsp;</div>
        <div class="modal-v-offset">
            <div class="modal-window">
                <div class="modal-content"></div>
                <p><button class="modal-close">Close</button></p>
            </div>
        </div>
    </aside>

    <!-- Terminal -->
    <div id="terminal"></div>

    <script src="js/jquery-1.8.3.min.js"></script>
    <script src="v/latest/js/Ptty.jquery.js"></script>
    <script>
    $(document).ready(function() {

        /* Start Ptty terminal */ 
        $('#terminal').Ptty({
            welcome : 'Welcome to the Ptty demo. Type <code>help</code> to start.'
        });

    });
    // Register commands
    load_commands();

    // Register callbacks
    $.register_callback('redirect', function(data){
        if(typeof data.url !== 'undefined'){
            window.location = data.url;
        }
    });

    $.register_callback('dialog', function(data){
        if(typeof data.html !== 'undefined'){
            $('.modal-content').html(data.html);
            $('.modal-wrap').show(200);
        }
    });

    /* Wrapper function to load commands */
    function load_commands(){
        // Register commands
        $.register_command(
            'ip',
            'Gets your remote IP address.',
            'ip [-v6]',
            ''
        );
        $.register_command(
            'fortune', 
            'Prints a random fortune cookie', 
            'fortune [-joke | -wisdom]',
            'ajax/fortune.php'
        );
        $.register_command(
            'date', 
            'Shows the time and date.', 
            'date [ _d_/_m_/_y_ _h_:_i_:_s_ ]', 
            function(args){
                return {
                    type : 'print', 
                    out : cmd_date(args)
                }
            }
        );
        $.register_command(
        'game', 
        'Plays rock-paper-scissors.', 
        'game [no options]', 
        {
            ps : 'game',
            start_hook : function(){ 
                return {
                    type : 'print', 
                    out : cmd_game(false, 'start')
                }; 
            },
            exit_hook : function(){ 
                return {
                    type : 'print',
                    out : cmd_game(false, 'end')
                }; 
            },
            dispatch_method : function(args){
                return {
                    type : 'print',
                    out : cmd_game(args, false)
                };
            }
        });

        var kitty_api = [{
                cmd_name        : 'ktyput',
                cmd_description : 'Saves a kitten pic to API', 
                cmd_usage       : 'kittyput [URL to image]',
                cmd_url         : 'ajax/kittyapi.php?put'
            },
            {
                cmd_name        : 'ktyget',
                cmd_description : 'Gets a kitty pic from API. Try a number from 01 to 10.', 
                cmd_usage       : 'kittyget [01 to 10]',
                cmd_url         : 'ajax/kittyapi.php?get'
            },
            {
                cmd_name        : 'ktydel',
                cmd_description : 'Deletes a kitty :-(.', 
                cmd_usage       : 'kittydel [01 to 10]',
                cmd_url         : 'ajax/kittyapi.php?del'
            },
            {
                cmd_name        : 'ktyreset',
                cmd_description : 'Brings the original kittys back from heaven.', 
                cmd_usage       : 'kittyreset [no options]',
                cmd_url         : 'ajax/kittyapi.php?reset'
        }];

        for (var i = kitty_api.length - 1; i >= 0; i--) {
            $.register_command(
                kitty_api[i].cmd_name,
                kitty_api[i].cmd_description, 
                kitty_api[i].cmd_usage,
                kitty_api[i].cmd_url
            );
        };

        $.register_command(
            'tutorial', 
            'A program that asks a lot of questions.', 
            'tutoarial [no options]', 
            {
                ps              : '~ttrl',
                start_hook      : 'ajax/tutorial.php?start',
                exit_hook       : 'ajax/tutorial.php?end',
                dispatch_method : 'ajax/tutorial.php'
        });

        $.register_command(
            'theme',
            'List themes or set a theme of your choice.',
            'theme [theme-name]',
            function(tokens){
                var output = '<p>Choose a theme:</p><ul><li>boring</li><li>pony</li><li>fallout</li><li>space</li></ul>';
                if(tokens.length > 1){
                    var Ptty = $('#terminal'),
                        theme_name = tokens[1];
                    if(theme_name == 'boring' || theme_name == 'pony' || theme_name == 'fallout' || theme_name == 'space'){
                        Ptty.removeClass (function (index, css) {
                            // Remove all classes that start with "cmd_terminal_theme_"
                            return (css.match (/(^|\s)cmd_terminal_theme_\S+/g) || []).join(' ');
                        });
                        // Add class
                        Ptty.addClass('cmd_terminal_theme_'+theme_name.toLowerCase());   
                        output = 'Theme changed to '+theme_name+'.';     
                    }
                    
                }
                return {
                    type : 'print',
                    out  : output
                }                
            }
        );

        // It's a register commands inception!
        $.register_command(
            'flush-commands',
            'Removes all the non native commands from Ptty.',
            'flush-commands [no options]',
            function(){
                $.flush_commands();
                $.register_command(
                    'reset-commands',
                    'Brings back all the commands you just removed.',
                    'reset-commands [no options]',
                    function(){
                        load_commands();
                        return {
                            type : 'print',
                            out  : 'Commands Reloaded. Type <code>help</code> or press tab to see them.'
                        }
                    }
                );
                return {
                    type : 'print',
                    out  : 'Commands Removed. Type <code>reset-commands</code> to bring them back.'
                }      
            }
        );
    }

    // Some unimportant functions...
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
<?php 
if($_SERVER['HTTP_HOST'] == 'code.patxipierce.com' && !isset($_COOKIE['trackmenot'])){
?>

    // Google Analytics
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-34241501-4', 'auto');
    ga('require', 'displayfeatures');
    ga('require', 'linkid', 'linkid.js');
    ga('send', 'pageview');
<?php 
}
?>
    </script>
</body>
</html>
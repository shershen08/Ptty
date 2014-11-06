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
    $expires = time() + (3600 * 24 * 30 * 3); // 3 months, take or give.
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
    setcookie('trackmenot', 'trackmenot', $expires, '/', $_SERVER['HTTP_HOST'], $secure, true);    
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ptty terminal emulator - jQuery Plugin</title>
    <link rel="icon" type="image/png" href="favicon.png" />
    <!--[if IE]><link rel="shortcut icon" href="favicon.ico"/><![endif]-->
    <link rel="stylesheet" type="text/css" href="css/lemonade.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="v/latest/css/Ptty.css" />
    <link rel="stylesheet" type="text/css" href="css/themes.css" />
</head>
<body>
    <aside class="modal-wrap">
        <div class="modal-overlay">&nbsp;</div>
        <div class="modal-v-offset">
            <div class="modal-window">
                <div class="modal-content"></div>
                <p><button class="modal-close">Close</button></p>
            </div>
        </div>
    </aside>

    <header class="frame">
        <div class="bit-2">
            <ul class="dropdown">
                <li><a href="#ptty">Ptty</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#demo">Demo</a>
                    <ul>
                        <li><a href="./demo">Full screen</a></li>
                    </ul>
                </li>
                <li><a href="#download">Download</a>
                    <ul>
                        <li><a href="#0.0.1">Version 0.0.1</a></li>
                        <li><a href="#0.0.2">Version 0.0.2</a></li>
                    </ul>
                </li>
                <li><a href="#themes">Themes</a></li>
                <li><a href="#usage">Usage</a>
                    <ul>
                        <li><a href="#options">Options</a></li>
                        <li><a href="#commands">Adding Commands</a></li>
                        <li><a href="#response">Response Format</a></li>
                        <li><a href="#callbacks">Adding Callbacks</a></li>
                        <li><a href="#other">Other Methods</a></li>
                    </ul>
                </li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#bugs">Bugs</a></li>
            </ul>
        </div>
        <div class="bit-2">
            <span id="title">Ptty terminal emulator</span>
        </div>
    </header>

    <div class="frame">
        <div class="bit-2">
            <article>
                <h1 id="ptty">Ptty jQuery Plugin</h1>
                <h3 id="description">Because command lines are cool.</h3>
            </article>

            <article>
                <h2 id="about"><a href="#about" class="headerlink" title="Permalink"></a>About</h2>
                <p><abbr title="Pseudo Teletype">Ptty</abbr> <span class="phonetic">/ˈpɪti/</span> is a web based terminal emulator plugin for <a class="external-link" href="https://jquery.com/" title="jQuery Official Site">jQuery</a>. It is based on <a class="external-link" href="http://wterminal.appspot.com/" title="Wterm terminal emulator">Wterm</a> by Venkatakirshnan Ganesh but has been modified to include a large set of new features.</p>
                <p>The list of features includes (but is not limtied to) a password prompt, and a <a class="anchor-link" href="#response">JSON response schema</a> to send commands to the terminal and execute custom callbacks.</p>
            </article>

            <article>
                <h2 id="demo"><a href="#demo" class="headerlink" title="Permalink"></a>Demo</h2>
                <p><b>Ptty</b> comes with three preset commands registered.</p>
                <p>There is also a full screen Demo. <a class="local-link" href="./demo" title="Fullscreen Demo">Try it out!</a></p>
                <p>Or... You can also stick around here and use these buttons to execute commands while you read the docs.</p>
                <p>
                    <button class="cmd" data-cmd="help">help</button>
                    <button class="cmd" data-cmd="clear">clear</button>
                    <button class="cmd" data-cmd="history">history</button>
                </p>
                <p>Type <code>help -a</code> in the box to your right and you will see all the available commands, their descriptions and their usage. The rest of the available commands in this demo are explained in the <a class="anchor-link" href="#usage">Usage</a> section.</p>
                <p>Oh, and you can also use the tab key and the up and down arrows to autocomplete commands and navigate through history.</p>
            </article>

            <article>
                <h2 id="download"><a href="#download" class="headerlink" title="Permalink"></a>Download</h2>
                <ul class="h-list">
                    <li class="collapsed"><h3 id="0.0.1"><a href="#0.0.1" class="headerlink" title="Permalink"></a>Version 0.0.1</h3>
                        <p>Extendible commands. Callback methods. Command History, help and clear commands.</p>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.1/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (32kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.1/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (8.9kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.1/css/Ptty.css" download="Ptty.css">Ptty.css (3.2kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package</h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.1/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (12.1 kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="collapsed"><h3 id="0.0.2"><a href="#0.0.2" class="headerlink" title="Permalink"></a>Version 0.0.2</h3>
                        <p>Upload capabilities. Encoding options.</p>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.2/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (32kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.2/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (8.9kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.2/css/Ptty.css" download="Ptty.css">Ptty.css (3.4kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package</h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.2/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (12kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="expanded"><h3 id="latest"><a href="#latest" class="headerlink" title="Permalink"></a>Latest Version</h3>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/latest/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (32kb)</a></li>
                                    <li><a class="download-link" href="v/latest/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (8.9kb)</a></li>
                                    <li><a class="download-link" href="v/latest/css/Ptty.css" download="Ptty.css">Ptty.css (3.4kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package</h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/latest/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (12kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </article>

            <article>
                <h2 id="themes"><a href="#themes" class="headerlink" title="Permalink"></a>CSS Themes</h2>
                <p>Ptty comes ready made to be themed like any modern terminal. This is the basic (default) theme:</p>
                <pre><code>/* boring theme */
.cmd_terminal_theme_boring,
.cmd_terminal_theme_boring input { background-color: #0a0a0a; color: #ddd; letter-spacing: 1px; }
.cmd_terminal_theme_boring .cmd_terminal_active { font-weight: bold; }
.cmd_terminal_theme_boring .cmd_terminal_ps span::after { content: ">"; }
.cmd_terminal_theme_boring .cmd_terminal_sub span::after { content: "\0000a0>"; }</code></pre>

                <p>But there is no need to be so bland, try these alternatives! (Or <a class="local-link" href="./css/themes.css">look at their css</a>)</p>
                <p>
                    <button id="fallout">Fallout</button> <button id="pony">Pony</button>
                    <button id="space">Space</button> <button id="boring">Boring</button>
                </p>
                <p>To make your own theme, just copy the template above and replace the theme name "boring" with your theme name, remember to add the <code>theme</code> <a class="anchor-link" href="#options">option</a> with your theme name when invoking Ptty.</p>
            </article>

            <article>
                <h2 id="usage"><a href="#usage" class="headerlink" title="Permalink"></a>Usage</h2>
                <p>The good part about this is that its dead simple. Take a look at the options list under these examples for a quick start.</p>
                <p>Basic usage:</p>
                <pre><code>$(document).ready(function(){
    $('#terminal').Ptty();
});</code></pre>

                <p>Usage with options:</p>
                <pre><code>$(document).ready(function(){
    $('#terminal').Ptty({
        // Default ajax URL (can be relative or absolute).
        url    : 'ajax/',

        // Set the PS to an empty string and change the 
        // defaults to use a custom <a class="anchor-link" href="#themes">css theme</a>.
        ps     : '',
        theme  : 'boring',
        welcome: 'Welcome to the matrix.'
    });
});</code></pre>

                <h3 id="options"><a href="#options" class="headerlink" title="Permalink"></a>Options</h3>
                <p>Ptty comes with several options that make it special.</p>
                <ul class="opt-list">
                    <li><code>url</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> current URL</li>
                            <li><span>Description:</span> The standard url that should be used to make requests. Defaults to same file.</li>
                        </ul>
                    </li>
                    <li><code>method</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> POST </li>
                            <li><span>Description:</span> The HTTP Method that must be used for Ajax Requests</li>
                        </ul>
                    </li>
                    <li><code>param</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> cmd </li>
                            <li><span>Description:</span> The GET/POST parameter that should be used to make requests</li>
                        </ul>
                    </li>
                    <li><code>tty_class</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> cmd_terminal </li>
                            <li><span>Description:</span> Class of the the primary terminal container (the Ptty.css files uses this class, be warned.)</li>
                        </ul>
                    </li>
                    <li><code>ps</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> An empty string</li>
                            <li><span>Description:</span> The Primary Prompt</li>
                        </ul>
                    </li>
                    <li><code>theme</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> boring </li>
                            <li><span>Description:</span> The theme that is applied by default</li>
                        </ul>
                    </li>
                    <li><code>width</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> 100% </li>
                            <li><span>Description:</span> Set the width of the terminal container</li>
                        </ul>
                    </li>
                    <li><code>height</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> 100% </li>
                            <li><span>Description:</span> Set the height of the terminal container</li>
                        </ul>
                    </li>
                    <li><code>welcome</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> Ptty v.0.0.1 </li>
                            <li><span>Description:</span> Message to be shown when the terminal is first started</li>
                        </ul>
                    </li>
                    <li><code>placeholder</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> * </li>
                            <li><span>Description:</span> The placeholder to echo in place of password input (see <a class="anchor-link" href="#response">response format</a>).</li>
                        </ul>
                    </li>
                    <li><code>not_found</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> CMD: Command Not Found </li>
                            <li><span>Description:</span> When command is not found: "CMD" will be replaced with the command.</li>
                        </ul>
                    </li>
                    <li><code>error_prefix</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> An Error Occured: </li>
                            <li><span>Description:</span> Prefix for error messages</li>
                        </ul>
                    </li>
                    <li><code>autocomplete</code>
                        <ul>
                            <li><span>Type:</span> bool</li>
                            <li><span>Default:</span> true </li>
                            <li><span>Description:</span> Is Autocomplete feature Enabled</li>
                        </ul>
                    </li>
                    <li><code>history</code>
                        <ul>
                            <li><span>Type:</span> bool</li>
                            <li><span>Default:</span> true </li>
                            <li><span>Description:</span> Is Command History Enabled</li>
                        </ul>
                    </li>
                    <li><code>history_max</code>
                        <ul>
                            <li><span>Type:</span> int</li>
                            <li><span>Default:</span> 800 </li>
                            <li><span>Description:</span> Number of entries to be stored in history</li>
                        </ul>
                    </li>
                    <li><code>charset</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> UTF-8</li>
                            <li><span>Description:</span> The character set to be used on the "accept-charset" form attribute</li>
                        </ul>
                    </li>
                    <li><code>enctype</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> multipart/form-data</li>
                            <li><span>Description:</span> The value of the "enctype" form attribute</li>
                        </ul>
                    </li>
                    <li><code>autofocus</code>
                        <ul>
                            <li><span>Type:</span> bool</li>
                            <li><span>Default:</span> true</li>
                            <li><span>Description:</span> When true the terminal will focus on load</li>
                        </ul>
                    </li>
                </ul>

                <h3 id="commands"><a href="#commands" class="headerlink" title="Permalink"></a>Adding Commands</h3>
                <p>Commands can be added easy enough using the <code>$.register_command()</code> method. It has 4 parameters:</p>
                <ul class="opt-list">
                    <li><code>command name</code>
                        <ul>
                            <li>The name with which to invoque the command thrugh the command line.</li>
                        </ul>
                    </li>
                    <li><code>command description</code>
                        <ul>
                            <li>A short note about what the command does.</li>
                        </ul>
                    </li>
                    <li><code>command usage</code>
                        <ul>
                            <li>How to use the command, options, etc...</li>
                        </ul>
                    </li>
                    <li><code>command dispatch</code>
                        <ul>
                            <li>This parameter can be a method, object or string. And is explained in the examples bellow.</li>
                        </ul>
                    </li>
                </ul>
                <p>To add a command that makes an AJAX request you could do the following:</p>
                <pre><code>$.register_command(
    'ip', 
    'Gets your remote IP address.', 
    'ip [-v6]',
    ''
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="ip">ip</button>
                    <button class="cmd" data-cmd="ip -v6">ip v6</button>
                </p>
                <p>This particular example will call this file with a POST request containing the input string because the last parameter is set to an empty string.</p>
                
                <p>Here is an example of the fourth parameter of <code>$.register_command()</code> using a URL. It can be relative or absolute.</p>
                <pre><code>$.register_command(
    'fortune', 
    'Prints a random fortune cookie', 
    'fortune [-joke | -wisdom]',
    'ajax/fortune.php'
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="fortune">fortune</button>
                </p>
                
                <p>You can also add commands directly in JavaScript, like this one:</p>
                <pre><code>$.register_command(
    'date', 
    'Shows the time and date.', 
    'date or date [ _d_/_m_/_y_ _h_:_i_:_s_ ]', 
    function(args){
        return {
            type : 'print',
            out      : cmd_date(args);
        }
    }
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="date">date</button>
                </p>

                <p>If you would like to register many commands (for example from an API), its as easy as looping through an object with the names and URLs for the commands like this:</p>
                <pre><code>var kitty_api = [{
        cmd_name        : 'ktyput',
        cmd_description : 'Saves a kitten pic to API', 
        cmd_usage       : 'kittyput [URL to image]',
        cmd_url         : 'kittyapi.php?put'
    },
    {
        cmd_name        : 'ktyget',
        cmd_description : 'Gets a kitty pic from API. Try a number from 01 to 10.', 
        cmd_usage       : 'kittyget [01 to 10]',
        cmd_url         : 'kittyapi.php?get'
    },
    {
        cmd_name        : 'ktydel',
        cmd_description : 'Deletes a kitty :-(.', 
        cmd_usage       : 'kittydel [01 to 10]',
        cmd_url         : 'kittyapi.php?del'
    },
    {
        cmd_name        : 'ktyreset',
        cmd_description : 'Brings the original kittys back from heaven.', 
        cmd_usage       : 'kittyreset [no options]',
        cmd_url         : 'kittyapi.php?reset'
}];

for (var i = kitty_api.length - 1; i >= 0; i--) {
    $.register_command(
        kitty_api[i].cmd_name,
        kitty_api[i].cmd_description, 
        kitty_api[i].cmd_usage,
        kitty_api[i].cmd_url
    );
};</code></pre>
                <p>
                    <button class="cmd" data-cmd="ktyput <?php echo $url; ?>/img/kitties/test/ktytest.png">ktyput</button>
                    <button class="cmd" data-cmd="ktyget <?php echo $random_kitty; ?>">ktyget</button>
                    <button class="cmd" data-cmd="ktydel <?php echo $random_kitty; ?>">ktydel</button>
                    <button class="cmd" data-cmd="ktyreset">ktyreset</button>
                </p>
                <p>But wait, there is more...</p>
                <p><b>Introducing Subroutines</b>: Subroutines run under the current Ptty instance and signal exit and start flags. To register a subroutine you must declare an object with four properties on the 4th parameter.</p>
                <pre><code>$.register_command(
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
    }
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="game">start game</button>
                    <button class="cmd" data-cmd="r">rock</button>
                    <button class="cmd" data-cmd="p">paper</button>
                    <button class="cmd" data-cmd="s">scissors</button>
                    <button class="cmd" data-cmd="exit">quit game</button>
                </p>
                <p>Here is a brief desctription of the subroutine properties:</p>
                <ul class="opt-list">
                    <li><code>ps</code>
                        <ul>
                            <li>Will set the PS1 of the subroutine, there is also a css class named <code>.cmd_terminal_sub</code> that you can use to further personalize your subroutine using a <a class="anchor-link" href="#themes">theme</a>.</li>
                        </ul>
                    </li>
                    <li><code>start_hook</code>
                        <ul>
                            <li>This method / request is made when the subroutine is first called.</li>
                        </ul>
                    </li>
                    <li><code>exit_hook</code>
                        <ul>
                            <li>The exit hook is called when the subroutine exits using the quit or exit commands. It is not called if the exit is declared in the <a class="anchor-link" href="#response">response</a>.</li>
                        </ul>
                    </li>
                    <li><code>dispatch_method</code>
                        <ul>
                            <li>This method or request is used every time the user enters a command in the subroutine.</li>
                        </ul>
                    </li>
                </ul>

                <p>Just one last example to demonstrate most of Ptty's features. In this case we use a subroutine with an AJAX call:</p>
                <pre><code>$.register_command(
    'tutorial', 
    'A program that asks a lot of questions.', 
    'tutorial [no options]', 
    {
        ps              : '~ttrl',
        start_hook      : 'tutorial.php?start',
        exit_hook       : 'tutorial.php?end',
        dispatch_method : 'tutorial.php'
    }
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="tutorial">Start tutorial</button>
                </p>
                <p>Take your time and play with it in the Demo terminal, the <code>tutorial</code> command will show you what can be done with very little effort and loading a bite sized request to speed everything up.</p>

                <h3 id="response"><a href="#response" class="headerlink" title="Permalink"></a>Response Format</h3>
                <p>As you might have noticed all the above functions return objects. This is a required format so Ptty knows what to do with your response. A typical response looks like this:</p>
                <pre><code>{ "type" : "print", "out" : "All that is spoken, is spoken by someone.", "exit" : true }</code></pre>
                <p>The properties are used to define the behaviour of the terminal. The response of a command must be a Javascript Object and can include the following properties:</p>
                
                <ul class="opt-list">
                    <li><code>out</code>
                        <ul><li>This value must be a string with the data to output on the command line.</li></ul>
                    </li>
                    <li><code>quiet</code>
                        <ul><li>Don't echo user input to the command line.</li></ul>
                        <ul>
                            <li><span>Values:</span>
                                <ul>
                                    <li><code>password</code>
                                        <ul>
                                            <li>Use this value to echo a placeholders instead of the password.</li>
                                        </ul>
                                    </li>
                                    <li><code>blank</code>
                                        <ul>
                                            <li>Leaves the output blank.</li>
                                        </ul>
                                    </li>
                                    <li><code>clear</code>
                                        <ul>
                                            <li>Wipes out the entire command line.</li>
                                        </ul>
                                    </li>
                                    <li><code>output</code>
                                        <ul>
                                            <li>Will not register the comand executed. But will display whatever was passed in the <code>out</code> property.</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><code>query</code>
                        <ul><li>What command or options to submit with the current command.</li></ul>
                    </li>
                    <li>
                        <code>token</code>
                        <ul><li>Used when doing information transactions, tokens when used well can give extra security to your interface.</li></ul>
                    </li>
                    <li>
                        <code>exit</code>
                        <ul><li>Exits a subroutine without user interaction (i.e. the user gets kicked out).</li></ul>
                    </li>
                    <li>
                        <code>type</code>
                        <ul><li>There are several callback types (passed as strings) that you can use to interact with Ptty.</li></ul>
                         <ul>
                            <li><span>Values:</span>
                                <ul>
                                    <li><code>print</code>
                                        <ul>
                                            <li>Will output a the <code>out</code> response property and exit to the current routine or subroutine.</li>
                                        </ul>
                                    </li>
                                    <li><code>prompt</code>
                                        <ul>
                                            <li>This will generate a token and send it to the server under the <code>cmd_token</code> parameter. You can also specify a custom token by using the <code>token</code> response property.</li>
                                        </ul>
                                    </li>
                                    <li><code>password</code>
                                        <ul>
                                            <li>Will replace the command input for a password field. This callback type should be used in combination with the <code>quiet</code> property.</li>
                                        </ul>
                                    </li>
                                    <li><code><i>custom callback</i>...</code>
                                        <ul>
                                            <li>A pre-defined callback can be called by adding callbacks with the <code>$.register_callback()</code> method (see <a class="anchor-link" href="#callbacks">adding callbacks</a> for more information).</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>

                <p>Manipulating Ptty seems harder than it really is, but for the sake of clarity here is a tutorial to master Ptty's powers.</p>
                <p>(You might want to open your console while the tutorial takes place, so you don't miss the juicy bits.)</p>

                <h3 id="callbacks"><a href="#callbacks" class="headerlink" title="Permalink"></a>Adding Callbacks</h3>
                <p><b>Ptty</b> can add custom callbacks just as easy as it adds commands, you will have to define the callback and load it but after that, they become available to any command using the <code>type</code> property.</p>
                <p>Callbacs are added using the <code>$.register_callback()</code> method. Here is an example redirect callback and modal dialog example.</p>

                <pre><code>$.register_callback('redirect', function(data){
    if(typeof data.url !== 'undefined'){
        window.location = data.url;
    }
});

$.register_callback('dialog', function(data){
    if(typeof data.html !== 'undefined'){
        $('.modal-content').html(data.html);
        $('.modal-wrap').show(500);
    }
});</code></pre>
                <p>To test them we are going to use our kitty API</p>
                <p>
                    <button class="cmd" data-cmd="ktyget <?php echo $random_kitty; ?> dialog">Dialog</button>
                    <button class="cmd" data-cmd="ktyget <?php echo $random_kitty; ?> redirect">Redirect</button>
                <p>
                <p>As you can see the parameters of the command response are handed down in full to the callback, so all the properties mentioned above can be present.</p>
                <p>The idea of using callbacks is to connect the user input with jQuery plugins like sliders, modal windows and other useful display tools.</p>

                <h3 id="other"><a href="#other" class="headerlink" title="Permalink"></a>Other Methods</h3>
                <p>You can reset all the commands recorded and add new ones too! It's as simple as calling:</p>
                <pre><code>$.flush_commands()</code></pre>
                <p>
                    <button class="flush-commands">Unregister Commands</button>
                    <button class="reload-commands">Reload Commands</button>
                </p>
                <p>After you click on the button above all the commands should have been removed except for the basic three (<code>help</code>, <code>history</code>, and <code>clear</code>). You can check by typing <code>help</code> or pressing the tab key on the terminal to your right.</p>
                <p>You can reload commands using the <code>$.register_command()</code> method again.</p>
                <p>Click on the button above to get the commands back. This is useful if, for example a loged in user has different set of commands available than a guest user.</p>

                <p>And to set an option of a command that has already been sent or to alter the options of a future command you can use the <code>$.set_command_option()</code> method.</p>
                <p>This method will override the input from a response and accepts the following options:</p>
                <pre><code>$.set_command_option({
    // If set, edits the subroutine name
    cmd_name  : null,
    // Command class
    cmd_class : null,
    // The ps value
    cmd_ps    : null,
    // The command string
    cmd_in    : null,
    // The output of the command.
    cmd_out   : null,
    // Set to 'password', 'clear' or 'blank'
    cmd_quiet : null, 
    // Set to a unique sting for secure transactions
    cmd_token : null, 
    // Acumulates a string for a subroutine to use
    cmd_query : null,
})</code></pre>

                <p>You can also use <code>$.get_command_option()</code> that will return the value of a command options requested using an array:</p>

                <pre><code>var current_opts = $.get_command_option(['cmd_token', 'cmd_query']);
// So now current_opts = {cmd_token : "<i>some value</i>", cmd_query : "<i>another value</i>"};</code></pre>

            </article>
        
            <article>
                <h2 id="faq"><a href="#faq" class="headerlink" title="Permalink"></a>FAQ</h2>
                
                <h3>Is Ptty on github?</h3>
                <p>Yes, <a class="external-link" href="https://github.com/pachanka/ptty" title="Ptty github resource">Ptty is on github</a>.</p>

                <h3>What public license type is Ptty under?</h3>
                <p>Ptty is licensed under the one and only <a class="external-link" href="http://wtfpl.net/" title="WTF Public License">Do What the Fuck You Want to Public License</a>.</p>

                <h3>What does that mean?</h3>
                <p>It means you can copy the source, and <i>do whatever you want to do with it</i>.</p>

<!--
                <h3>Is Ptty being used in the wild?</h3>
                <p>Yes. <a class="external-link" href="http://pachanka.org" title="Pachanka">pachanka.org</a> uses Ptty as its central library.</p>
-->
                <h3>Can I contact you about improvements, features, bugs, requests?</h3>
                <p>Yes of course. Please use the contact form at <a class="external-link" href="http://patxipierce.com" title="Patxi Pierce">patxipierce.com</a> and I'll answer as soon as possible.</p>
            </article>

            <article>
                <h2 id="bugs"><a href="#bugs" class="headerlink" title="Permalink"></a>Bugs</h2>
                <p>There are bugs, as it's an early realease, please <a class="external-link" href="http://patxipierce.com" title="Patxi Pierce">notify me</a> if you find anything that does not work as expected.</p>
                <ul>
                    <li><p>v.0.0.1</p>
                        <ul>
                            <li>* The scroll to bottom animation lags if several commands are sent rapidly.</li>
                            <li>* <strike>The history feature for subroutines is broken.</strike></li>
                            <li>* <strike>Terminal width expands with extreamly long commands.</strike></li>
                        </ul>
                    </li>
                </ul>

                <p>Luky for us, where there are bugs there are also features!!</p>
                <ul>
                    <li><p>v.0.0.2</p>
                        <ul>
                            <li>* Major code cleanup, the update_content() private function has been re-written.</li>
                            <li>* Added <code>$.get_command_option()</code> which returns the value for the property for a command option.</li>
                            <li>* Added <code>disabled="disabled"</code> to terminal input while loading.</li>
                            <li>* Added <code>charset</code> <code>enctype</code> <code>autofocus</code> to the available Ptty options.</li>
                            <li>* Added the <code>output</code> value to the <code>quiet</code> <a href="#response">response format</a> property.</li>
                        </ul>
                    </li>
                </ul>
            </article>
            <article id="ascii-flair">
                <pre>
    ______ _   _           
    | ___ \ | | |          
    | |_/ / |_| |_ _   _   
    |  __/| __| __| | | |  
    | |   | |_| |_| |_| |_ 
    \_|    \__|\__|\__, (_)
                    __/ |  
                   |___/   
                </pre>
                <p>( <small>This footer is the only reason I built this library. Really.</small> )</p>
            </article>
        </div>

        <div class="bit-2">
            <article>
                <div id="term-fixed">
                    <div id="terminal"></div>
                </div>    
            </article>
        </div>
    </div>
    <div class="github">
        <a href="https://github.com/pachanka/ptty" title="Fork me on GitHub" target="_blank">
            <img class="top" src="img/bottom-right-github-black-pink.png" alt="Fork me on GitHub" />
            <img class="bottom" src="img/bottom-right-github-black-pink-hover.png" alt="Fork me on GitHub" />
        </a>
    </div>

    <script src="js/jquery-1.8.3.min.js"></script>
    <script src="js/main.js"></script>
    <script src="v/latest/js/Ptty.jquery.js"></script>
    <script>
    $(document).ready(function() {

        /* Start Ptty terminal */ 
        $('#terminal').Ptty();
        
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
        

        // Themes
        $('#fallout, #pony, #space, #boring').click(function(){
            var theme_name = $(this).html();
            
            $("#terminal").removeClass (function (index, css) {
                // Remove all classes that start with "cmd_terminal_theme_"
                return (css.match (/(^|\s)cmd_terminal_theme_\S+/g) || []).join(' ');
            });

            // Add class
            $('#terminal').addClass('cmd_terminal_theme_'+theme_name.toLowerCase());
            $('#terminal').find('input[type=text]').focus();
        });

        $('button.cmd').click(function(){
            var form = $("#terminal").find('form');
            var cmd = $(this).attr('data-cmd');
            form.find('input[type=text]').val(cmd);
            form.submit();
        });

        $('.flush-commands').click(function(){
            $.flush_commands();
            alert('Commands unregistered. Type help in the demo to check it out.');
        });

        $('.reload-commands').click(function(){
            load_commands();
            alert('Commands reloaded');
        });
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

    var clicks = 0;
    $('a').on('click', function() {
        var dl = $(this).attr('download'),
            href = $(this).attr('href'),
            category,
            label;
        clicks++;
        if(typeof dl !== 'undefined'){
            category = 'download';
            label = dl;
        }else{
            if(href.charAt(0) == '#'){
                category = 'anchor';
            }else{
                category = 'link';
            }
            label = href;
        }
        ga('send', 'event', category, 'click', label, clicks);
    });
<?php 
}
?>
    </script>
</body>
</html>
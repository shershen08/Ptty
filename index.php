<?php
if(isset($_POST["type"]) && isset($_POST["upload_accept"])){
    $out = array('type' => 'print', 'out' => 'Never mind uploading, you get the point.' );
    header('Content-Type: application/json');
    die(json_encode($out));
}
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
        }else if($in[0] == 'register'){
            $out['out'] = 'Successful fake registation!';
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
    <link rel="stylesheet" type="text/css" href="css/prism.css" />
    <link rel="stylesheet" type="text/css" href="v/latest/css/ptty.css" />
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
                        <li><a href="#0.0.3">Version 0.0.3</a></li>
                        <li><a href="#latest">Latest 0.0.4</a></li>
                    </ul>
                </li>
                <li><a href="#themes">Themes</a></li>
                <li><a href="#usage">Usage</a>
                    <ul>
                        <li><a href="#options">Options</a></li>
                        <li><a href="#commands">Adding Commands</a></li>
                        <li><a href="#subroutines">Subroutines</a></li>
                        <li><a href="#response">Response Format</a></li>
                        <li><a href="#callbefores">Adding Callbefores</a></li>
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
                <p><abbr title="Pseudo Teletype">Ptty</abbr> <span class="phonetic">/ˈpɪti/</span> is a web based terminal emulator plugin for <a class="external-link" href="https://jquery.com/" title="jQuery Official Site">jQuery</a>. It was originally based on <a class="external-link" href="https://github.com/gvenkat/wterm" title="Wterm terminal emulator">Wterm</a> by Venkatakirshnan Ganesh but has been modified to include a large set of new features.</p>
                <p>The list of features includes (but is not limited to) a password prompt, and a <a class="anchor-link" href="#response">JSON response schema</a> to send commands to the terminal and execute custom callbacks.</p>
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
                        <p>Extensible commands. Callback methods. Command History, help and clear commands.</p>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.1/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (32kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.1/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (8.9kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.1/css/Ptty.css" download="Ptty.css">Ptty.css (3.2kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package <small>(md5 d4b4212b5f5ed67039e9b6b7c9a3a7c7)</small></h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.1/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (12.1 kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="collapsed"><h3 id="0.0.2"><a href="#0.0.2" class="headerlink" title="Permalink"></a>Version 0.0.2</h3>
                        <p>Encoding options. Cleanup. Experimental features.</p>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.2/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (32kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.2/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (8.9kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.2/css/Ptty.css" download="Ptty.css">Ptty.css (3.4kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package <small>(md5 a834d1be15cc42e9d2ce663a148c5c79)</small></h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.2/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (12kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="collapsed"><h3 id="0.0.3"><a href="#0.0.3" class="headerlink" title="Permalink"></a>Version 0.0.3</h3>
                        <p>AJAX Upload capability, fixed history bug and others.</p>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.3/js/Ptty.jquery.js" download="Ptty.jquery.js">Ptty.jquery.js (41.7.4kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.3/js/Ptty.jquery.min.js" download="Ptty.jquery.min.js">Ptty.jquery.min.js (9.9kb)</a></li>
                                    <li><a class="download-link" href="v/0.0.3/css/Ptty.css" download="Ptty.css">Ptty.css (3.5kb)</a></li>   
                                </ul>
                            </li>
                            <li><h4>Package <small>(md5 a175f9a96441f64ac2add2324d58d740)</small></h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/0.0.3/Ptty.jquery.tar.gz" download="Ptty.jquery.tar.gz">Ptty.jquery.tar.gz (13.5kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="expanded"><h3 id="latest"><a href="#latest" class="headerlink" title="Permalink"></a>Latest Version (v.0.0.4)</h3>
                        <ul>
                            <li><h4>Files</h4>
                                <ul>
                                    <li><a class="download-link" href="v/latest/js/ptty.jquery.js" download="ptty.jquery.js">ptty.jquery.js (41.7kb)</a></li>
                                    <li><a class="download-link" href="v/latest/js/ptty.jquery.min.js" download="ptty.jquery.min.js">ptty.jquery.min.js (11.6kb)</a></li>
                                    <li><a class="download-link" href="v/latest/css/ptty.css" download="ptty.css">ptty.css (3.8kb)</a></li>
                                </ul>
                            </li>
                            <li><h4>Package <small>(md5 998b461e56c16525f48475fa0e01cb61)</small></h4>
                                <p>Containing all the above plus an example file.</p>
                                <ul>
                                    <li><a class="download-link" href="v/latest/ptty.jquery.tar.gz" download="ptty.jquery.tar.gz">ptty.jquery.tar.gz (13.5kb)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </article>

            <article>
                <h2 id="themes"><a href="#themes" class="headerlink" title="Permalink"></a>CSS Themes</h2>
                <p>Ptty comes ready made to be themed like any modern terminal. This is the basic (default) theme:</p>
                <pre><code class="language-css">/* boring theme */
.cmd_terminal_theme_boring,
.cmd_terminal_theme_boring input { 
    background-color: #0a0a0a; color: #ddd; letter-spacing: 1px; 
}
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
                <pre><code class="language-javascript">$(document).ready(function(){
    $('#terminal').Ptty();
});</code></pre>

                <p>Usage with options:</p>
                <pre><code class="language-javascript">$(document).ready(function(){
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
                    <!-- Removed as of 0.0.4
                    <li><code>ps</code>
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> An empty string</li>
                            <li><span>Description:</span> The Primary Prompt</li>
                        </ul>
                    </li>
                    -->
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
                            <li><span>Default:</span> Ptty v.[version number] </li>
                            <li><span>Description:</span> Message to be shown when the terminal is first started</li>
                        </ul>
                    </li>
                    <li><code>placeholder</code> <!-- changed in 0.0.4 -->
                        <ul>
                            <li><span>Type:</span> str</li>
                            <li><span>Default:</span> ● </li>
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
                            <li><span>Default:</span> An Error Occurred: </li>
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
                            <li>The name with which to invoke the command through the command line.</li>
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
                <pre><code class="language-javascript">$.register_command(
    'ip', 
    'Gets your remote IP address.', 
    'ip [-v6]',
    ''
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="ip">ip</button>
                    <button class="cmd" data-cmd="ip -v6">ip v6</button>
                </p>
                <p>This particular example will call this same file ( <code>index.php</code> ) with a POST request containing the input string because the last parameter is set to an empty string.</p>
                
                <p>Here is an example of the fourth parameter of <code>$.register_command()</code> using a URL. It can be relative or absolute.</p>
                <pre><code class="language-javascript">$.register_command(
    'fortune', 
    'Prints a random fortune cookie', 
    'fortune [-joke | -wisdom]',
    'ajax/fortune.php'
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="fortune">fortune</button>
                    <button class="cmd" data-cmd="fortune -wisdom">wisdom</button>
                    <button class="cmd" data-cmd="fortune -joke">joke</button>
                </p>
                
                <p>You can also add commands directly in JavaScript, like this one that calls a <a href="https://gist.github.com/pachanka/7a56ea9f42708bfa1e9f#file-ptty_example_functions-js-L3">function</a> that returns the date:</p>
                <pre><code class="language-javascript">$.register_command(
    'date', 
    'Shows the time and date.', 
    'date or date [ _d_/_m_/_y_ _h_:_i_:_s_ ]', 
    function(args){
        return {
            type : 'print',
            out  : cmd_date(args)
        }
    }
);</code></pre>
                <p>
                    <button class="cmd" data-cmd="date">date</button>
                    <button class="cmd" data-cmd="date it's exactly _h_:_i_:_s_">formated</button>
                </p>

                <p>If you would like to register many commands (for example from an API), its as easy as looping through an object with the names and URLs for the commands like this:</p>
                <pre><code class="language-javascript">var kitty_api = [{
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

                <h3 id="subroutines"><a href="#subroutines" class="headerlink" title="Permalink"></a>Introducing Subroutines</h3>
                <p>Subroutines run under the current Ptty instance and signal exit and start flags. To register a subroutine you must declare an object with four properties on the 4th parameter.</p>
                <pre><code class="language-javascript">$.register_command(
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
                    <button class="cmd" data-cmd="game" onclick="$(this).prop('disabled', true).siblings(':disabled').prop('disabled', false);">start game</button>
                    <button class="cmd" data-cmd="r" disabled>rock</button>
                    <button class="cmd" data-cmd="p" disabled>paper</button>
                    <button class="cmd" data-cmd="s" disabled>scissors</button>
                    <button class="cmd" data-cmd="exit" disabled onclick="$(this).siblings().andSelf().prop('disabled', true).filter(':first').prop('disabled', false)">quit game</button>
                </p>

                <p>See the <code>cmd_game()</code> function <a href="https://gist.github.com/pachanka/7a56ea9f42708bfa1e9f#file-ptty_example_functions-js-L22">here</a>.</p>

                <p>Here is a brief description of the subroutine properties:</p>
                <ul class="opt-list">
                    <li><code>ps</code>
                        <ul>
                            <li>Will set the PS1 of the subroutine, there is also a CSS class named <code>.cmd_terminal_sub</code> that you can use to further personalize your subroutine using a <a class="anchor-link" href="#themes">theme</a>.</li>
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
                <pre><code class="language-javascript">$.register_command(
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
                <p>As you might have noticed all the above functions return objects. This is a required format so Ptty knows what to do with your response. A typical command will return an object with a <code>out</code> property for the output like this:</p>

                <pre><code class="language-javascript">$.register_command(
    'echo', 
    'Output to the command line', 
    'echo [text to output]', 
    function(args){
        args.shift();
        return {out : args.join(' ')};
});</code></pre>
                <button class="cmd" data-cmd="echo hello!">echo hello!</button>


                <p>The properties are used to define the behavior of the terminal.</p>
                <p>The response of a command must be a Javascript Object and can include the following properties:</p>
                
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
                                            <li>Use this value to echo placeholders instead of the password.</li>
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
                                            <li>Will not register the command executed. But will display whatever was passed in the <code>out</code> property.</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><code>query</code>
                        <ul><li>What command or options to submit with the current command.</li></ul>
                    </li>
                    <li><code>clean</code>
                        <ul><li>An important option that clears name, ps, or query properties on demmand.</li></ul>
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
                        <code>callback</code>
                        <ul>
                            <li>A pre-defined callback can be called by adding callbacks with the <code>$.register_callback()</code> method (see <a class="anchor-link" href="#callbacks">adding callbacks</a> for more information).</li>
                        </ul>
                    </li>
                    <li>
                        <code>type</code>
                        <ul><li>There are several callback types (passed as strings) that you can use to interact with Ptty.</li></ul>
                         <ul>
                            <li><span>Values:</span>
                                <ul>
                                    <li><code>print</code>
                                        <ul>
                                            <li>Will output a the <code>out</code> response property and exit to the current routine or subroutine. If no type is specified print will be used.</li>
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
                                    <li><code>upload</code>
                                        <ul>
                                            <li>Attempts to open the file selector input.
                                                <p>If this type is used you can also add the following properties to the base response format:</p>
                                                <ul>
                                                    <li><code>upload_to</code>, if set Ptty will upload to the designated URI. If it is not set the upload will be made to the main ajax URL or the subroutine URL. The upload is always done via POST method.</li>
                                                </ul>
                                                <p>These will have effect on the input file tag used for file selections.</p>
                                                <ul>
                                                    <li><code>upload_multiple</code> if set the multiple files will be allowed.</li>
                                                    <li><code>upload_accept</code> is the value of the accept attribute.</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>

                <p>Manipulating Ptty seems harder than it really is, but for the sake of clarity here are some more examples of objects that tell Ptty what to do:</p>

                <p>Here is a modified version to test responses directtly from the command line:</p>
                <pre><code class="language-javascript">$.register_command(
    'response',
    'Useful for testing Ptty response methods.', 
    'response [response object]', 
    function(args){
        args.shift();
        return JSON.parse(args.join(' '));
});</code></pre>

                <p>A response to ask a user what his name is and append it to an echo command:</p>
                <pre><code class="language-javascript">{
    "type" : "print",
    "out" : "Whats your name?",
    "query" : "echo hello ",
    "clean" : "query"
}</code></pre>
                <button class="cmd" data-cmd='response {"type": "print", "out" : "Whats your name?", "query" : "echo hello ", "clean" : "query"}'>Ask name</button>

                <p>Note the usage of <code>"query" : "echo thanks "</code> to concatenate the input after the next command and <code>"clean" : "query"</code>, to empty the query command which is <i>sticky</i> and will persist until cleared.</p>
                <p>Another example, a response to ask for a password:</p>
                <pre><code class="language-javascript">{
    "type" : "password",
    "ps" : "Secret",
    "query" : "echo your password is: ",
    "clean" : ["query","ps"]
}</code></pre>
                <button class="cmd" data-cmd='response {"type":"password","ps":"Secret","query":"echo your password is ","clean":["query","ps"]}'>Ask password</button>
                
                <p>Here the <code>clean</code> property is an array containing <code>["query","ps"]</code>, the properties to clean. Just like the previous example, this is required so the command options do not perpetuate on the next command you enter.</p>

                <p>Now lets check out the upload feature.</p>
                <pre><code class="language-javascript">{
    "type" : "upload"
    "upload_accept" : "image/*"
    "custom_field" : "custom_value"
}</code></pre>
                <button class="cmd" data-cmd='response {"type" : "upload", "upload_accept" : "image/*", "custom_field" : "custom_value"}'>Upload</button>

                <p>As you can see the upload posted your selected file. And the other parameters to this same file. You can use the <code>upload_to</code> property to choose where to upload.</p>
                
                <p>Please note that uploading files can have <u><strong>serious</strong></u> security implications.</p>

                <h3 id="callbefores"><a href="#callbefores" class="headerlink" title="Permalink"></a>Adding Callbefores</h3>
                
                <p>One of the problems with early versions of Ptty was processing the user input on client side before sending it over to the server through the built in AJAX functionality. A workaround to this problem is called a <code>callbefore</code> because it is the opposite of a <a href="#callbacks">callback</a>.</p>

                <p>Like callbacks, all callbefores must be registered to a <a href="#commands">registered command</a> to work, and should always return a <code>string</code> to continue or <code>false</code> to stop the command from being processed.</p>

                <p>Here is a fake registration validation command we will use to test this feature:</p>

                <pre><code class="language-javascript">$.register_command(
    'register', 
    'A fake registration command.', 
    'register [-u username -e email -p password]',
    ''
);

$.register_callbefore(
    'register',
    function(cmd){
        var err = false,
        cmd_opts = ['-u','-e','-p'],
        args = $.tokenize(cmd, cmd_opts);

        // replace password
        if(typeof args['-p'] !== 'undefined'){
            var stars = '*'.repeat(args['-p'].length),
            safe = cmd.replace(RegExp('\\s'+args['-p'], "g"), ' '+stars);
            $.set_command_option({ cmd_in : safe });
        }

        err = validate_registration(cmd_opts, args);
        if(err){
            // ouput errors
            $.set_command_option({ cmd_out : err });
            cmd = false;
        }

        return cmd;
    }
);</code></pre>
                
                <p>As you can see, the command entered is tokenized using <code>$.tokenize()</code> and then the validation errors are sent using <code>$.set_command_option()</code> (more about these two <a href="#other">here</a>).</p>
                
                <p>It then returns a string with the original command input. Or false if invalid data is detected.</p>

                <button class="cmd" data-cmd='register -u usr -e em@ai.ls -p secret'>Register</button>

                <p>Try running the command again (press the up arrow key), but this time delete a parameter (so to invalidate the registration). You should get an error message, and no AJAX post will be made. This is because the call before returned <code>false</code>.</p>

                <p><b><u>Important</u>:</b> When a callbefore returns a string, it will post it using the <code>cbf</code> parameter. It will also use the <code>cmd</code> parameter as a cache of the original command. For instance, the posted content of the example above would look like:</p>

                <pre><code class="language-css">cmd=register+-u+usr+-e+em@ai.ls+-p+******
cbf=register+-u+usr+-e+em@ai.ls+-p+secret</code></pre>

                <h3 id="callbacks"><a href="#callbacks" class="headerlink" title="Permalink"></a>Adding Callbacks</h3>
                <p><b>Ptty</b> can add custom callbacks just as easy as it adds commands, you will have to define the callback and load it, but after that they become available to any command using the <code>callback</code> property.</p>
                <p>Callbacks are added using the <code>$.register_callback()</code> method. Here is an example redirect callback and modal dialog example.</p>

                <pre><code class="language-javascript">$.register_callback('redirect', function(data){
        if(typeof data.url !== 'undefined' &amp;&amp; window.confirm("Redirect?")) { 
              window.location = data.url;
        }
        
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
                
                <pre><code class="language-javascript">$.flush_commands()</code></pre>
                
                <p>
                    <button class="flush-commands">Unregister Commands</button>
                    <button class="reload-commands">Reload Commands</button>
                </p>
                <p>After you click on the button above all the commands should have been removed except for the basic three (<code>help</code>, <code>history</code>, and <code>clear</code>). You can check by typing <code>help</code> or pressing the tab key on the terminal to your right.</p>
                <p>You can reload commands using the <code>$.register_command()</code> method again.</p>
                <p>Click on the button above to get the commands back. This is useful if, for example a logged in user has different set of commands available than a guest user.</p>

                <p>And to set an option of a command that has already been sent or to alter the options of a future command you can use the <code>$.set_command_option()</code> method.</p>

                <pre><code class="language-javascript">$.set_command_option({ cmd_ps : 'Are you sure?', cmd_clean : 'ps' })</code></pre>

                <p>This method will override the input from a response and accepts the following options:</p>

                <ul class="opt-list">
                    <li><code>cmd_name</code>
                        <ul><li>If set, edits the subroutine name.</li></ul>
                    </li>
                    <li><code>cmd_class</code>
                        <ul><li>Command class attribute value.</li></ul>
                    </li>
                    <li><code>cmd_ps</code>
                        <ul><li>The ps value, that precedes the command input.</li></ul>
                    </li>
                    <li><code>cmd_in</code>
                        <ul><li>The command input.</li></ul>
                    </li>
                    <li><code>cmd_out</code>
                        <ul><li>The output of the command.</li></ul>
                    </li>
                    <li><code>cmd_quiet</code>
                        <ul><li>Set to 'password', 'clear', 'output' or 'blank'</li></ul>
                    </li>
                    <li><code>cmd_token</code>
                        <ul><li>Set to a unique sting for secure transactions</li></ul>
                    </li>
                    <li><code>cmd_query</code>
                        <ul><li>Accumulates a string for a subroutine to use</li></ul>
                    </li>
                    <li><code>cmd_clean</code>
                        <ul><li>Clears the selected option</li></ul>
                    </li>
                </ul>

                <p>You might have noticed the <code>cmd_quiet</code> option uses the same values as the <a href="#response">response</a>.</p>

                <p>You can also use <code>$.get_command_option()</code> that will return the value of a command options requested using an array:</p>

                <pre><code class="language-javascript">var current_opts = $.get_command_option(['cmd_token', 'cmd_query']);
// So now current_opts = {cmd_token : "<i>some value</i>", cmd_query : "<i>another value</i>"};</code></pre>

                <p>Since Ptty 0.0.4 you can also use <code>$.tokenize()</code> that will attempt to tokenize a string in to its intended options, taking into account double quotes and single quotes.</p>

                <pre><code class="language-javascript">var tokens = $.tokenize('command --desc "Hello world"', ['--desc']);
// Returns { --desc : "Hello world" }
</code></pre>
                <p><strong><u>Note:</u></strong> This is still an experimental function and may not behave as expected.</p>

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
                <p>Yes of course. Please rise an <a class="external-link" href="https://github.com/pachanka/Ptty/issues" title="Patxi Pierce">issue</a> and I'll answer as soon as possible.</p>
            </article>

            <article>
                <h2 id="bugs"><a href="#bugs" class="headerlink" title="Permalink"></a>Bugs</h2>
                <p>There are bugs, as it's an early release, please <a class="external-link" href="http://patxipierce.com" title="Patxi Pierce">notify me</a> if you find anything that does not work as expected.</p>
                <!-- Most likely there will be no version 0.0.5
                <ul>
                    <li>
                        <h3>To-Do for version 0.0.5:</h3>
                        <ul>
                            <li>* Added optional support for <a href="#commands">commands</a> and <a href="#subroutines">subroutines</a> to provide autocomplete.</li>
                            <li>* Dealt with "scroll to bottom" lag bug.</li>
                            <li>* Fix <code>help()</code> autocomplete bug.</li>
                            <li>* History sometimes is in wacky order bug.</li>
                            <li>* Using the <code>contenteditable</code> attributes for input.</li>
                            <li>* Make cursors setting in CSS. Like <span class="term-cursor">▮</span> (U+25AE) and <span class="term-cursor">▯</span> (U+25AF) wouldent hurt.</li>
                            <li>* Changed default password placeholder to ● (U+25CF)</li>
                            <li>* The <code>ps</code> option was removed and now is handled through CSS.</li>
                        </ul>
                    </li>
                </ul>
                -->
                <ul>
                    <li class="collapsed">
                        <h3>Show earlier bugs and features added.</h3>
                        <ul>
                            <li><p>v.0.0.1</p>
                                <ul>
                                    <li>* The scroll to bottom animation lags if several commands are sent rapidly.</li>
                                    <li>* The history feature for subroutines was broken.</li>
                                    <li>* Terminal width expanded with extremely long commands.</li>
                                </ul>
                            </li>
                            <li><p>v.0.0.2</p>
                                <ul>
                                    <li>* Major code cleanup, the update_content() private function has been re-written.</li>
                                    <li>* Added <code>$.get_command_option()</code> which returns the value for the property for a command option.</li>
                                    <li>* Added <code>disabled="disabled"</code> to terminal input while loading.</li>
                                    <li>* Added <code>charset</code> <code>enctype</code> <code>autofocus</code> to the available Ptty options.</li>
                                    <li>* Added the <code>output</code> value to the <code>quiet</code> <a href="#response">response format</a> property.</li>
                                </ul>
                            </li>
                            <li><p>v.0.0.3</p>
                                <ul>
                                    <li>* Added upload functionality, using the <code>upload</code> <a href="#response">response type</a>.</li>
                                    <li>* Removed more bugs.</li>
                                </ul>
                            </li>
                            <li><p>v.0.0.4</p>
                                <ul>
                                    <li>* Added the <code>$.register_callbefore()</code> function <a href="#callbefores">response type</a>.</li>
                                    <li>* Added the <code>$.tokenize()</code> helper function.</li>
                                </ul>
                            </li>
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
                <p>( <small>I think we are done here.</small> )</p>
            </article>
        </div>

        <div class="bit-2">
            <article>
                <div id="term-fixed">
                    <div id="term-border">
                        <div id="terminal"></div>
                    </div>
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

    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/prism.js"></script>
    <script src="v/latest/js/ptty.jquery.js"></script>
    <script>
    $(document).ready(function() {

        /* Start Ptty terminal */ 
        $('#terminal').Ptty();
        
        // Register commands
        load_commands();
        
        // Register callbefores
        load_callbefores();

        // Register callbacks
        load_callbacks();
        
        // Example buttons behavior
        button_listeners();

    });

    /* Wrapper function to load commands */
    function load_commands(){

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
            }
        );

        $.register_command(
            'echo', 
            'Output to the command line', 
            'echo [text to output]', 
            function(args){
                args.shift();
                return {out : args.join(' ')};
            }
        );

        $.register_command(
            'response', 
            'Useful for testing Ptty response methods.', 
            'response [response object]', 
            function(args){
                args.shift();
                return JSON.parse(args.join(' '));
            }
        );

        $.register_command(
            'register', 
            'A fake registration command.', 
            'register [-u username -e email -p password]',
            ''
        );
    }

    function load_callbefores(){
        $.register_callbefore(
            'register',
            function(cmd){
                var err = false,
                cmd_opts = ['-u','-e','-p'],
                args = $.tokenize(cmd, cmd_opts);

                // replace password
                if(typeof args['-p'] !== 'undefined'){
                    var stars = '*'.repeat(args['-p'].length),
                    safe = cmd.replace(RegExp('\\s'+args['-p'], "g"), ' '+stars);
                    $.set_command_option({ cmd_in : safe });
                }

                err = validate_registration(cmd_opts, args);
                if(err){
                    // ouput errors
                    $.set_command_option({ cmd_out : err });
                    cmd = false;
                }

                return cmd;
            }
        );
    }

    /* Wrapper function to load callbacks */
    function load_callbacks(){

        $.register_callback(
            'redirect', function(data){
            if(typeof data.url !== 'undefined' && window.confirm("Redirect?")) { 
              window.location = data.url;
            }
        });

        $.register_callback(
            'dialog', function(data){
            if(typeof data.html !== 'undefined'){
                $('.modal-content').html(data.html);
                $('.modal-wrap').show(200);
            }
        });
    }

    /* Helper Functions */
    function validate_registration(cmd_opts, args){
        var err_msg = [];
        for (var i = cmd_opts.length - 1; i >= 0; i--) {
            if(typeof args[cmd_opts[i]] == 'undefined' || args[cmd_opts[i]] == ''){
                err_msg.push('Not enough parameters, type "help register" for more info.');
                break;
            }
        };

        if(err_msg.length == 0){
            if( typeof args['-u'] !== 'undefined' 
                && args['-u'].length < 3){
                err_msg.push('Minimum user name length is 3 characters.');
            }
            if ( typeof args['-e'] !== 'undefined'
                && /^[a-z0-9_\-.]+@[a-z0-9\-]+\.[a-z0-9\-.]+$/.test(args['-e']) === false ){
                err_msg.push('Invalid email.');
            }
            if(typeof args['-p'] !== 'undefined' 
                && args['-p'].length < 5){
                err_msg.push('Your password needs 5 characters as minimum.');
            }
        }

        if(err_msg.length > 0){
            return 'Error:<br>'+err_msg.join('<br>');
        }else{
            return false;
        }
    }

    /* Wrapper function to add listeners for button actions */
    function button_listeners(){

        $('#fallout, #pony, #space, #boring').click(function(){
            var theme_name = $(this).html();
            
            $("#terminal").removeClass (function (index, css) {
                // Remove all classes that start with "cmd_terminal_theme_"
                return (css.match (/(^|\s)cmd_terminal_theme_\S+/g) || []).join(' ');
            });
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
}else{
?>
/* Tracking code removed ^_^ */
<?php 
}
?>
    </script>
</body>
</html>
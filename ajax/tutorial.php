<?php


/*
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

*/

function wordsToNumber($data) {
    $data = strtr(
        $data,
        array(
            'zero'      => '0',
            'a'         => '1',
            'one'       => '1',
            'two'       => '2',
            'three'     => '3',
            'four'      => '4',
            'five'      => '5',
            'six'       => '6',
            'seven'     => '7',
            'eight'     => '8',
            'nine'      => '9',
            'ten'       => '10',
            'eleven'    => '11',
            'twelve'    => '12',
            'thirteen'  => '13',
            'fourteen'  => '14',
            'fifteen'   => '15',
            'sixteen'   => '16',
            'seventeen' => '17',
            'eighteen'  => '18',
            'nineteen'  => '19',
            'twenty'    => '20',
            'thirty'    => '30',
            'forty'     => '40',
            'fourty'    => '40',
            'fifty'     => '50',
            'sixty'     => '60',
            'seventy'   => '70',
            'eighty'    => '80',
            'ninety'    => '90',
            'hundred'   => '100',
            'thousand'  => '1000',
            'million'   => '1000000',
            'billion'   => '1000000000',
            'and'       => '',
        )
    );

    
    $parts = array_map( 'to_float', preg_split('/[\s-]+/', $data) );

    $stack = new SplStack; // Current work stack
    $sum   = 0; // Running total
    $last  = null;

    foreach ($parts as $part) {
        if (!$stack->isEmpty()) {
            if ($stack->top() > $part) {
                if ($last >= 1000) {
                    $sum += $stack->pop();
                    $stack->push($part);
                } else {
                    $stack->push($stack->pop() + $part);
                }
            } else {
                $stack->push($stack->pop() * $part);
            }
        } else {
            $stack->push($part);
        }
        $last = $part;
    }
    return $sum + $stack->pop();
}

// Helpers

function to_float($val) {
    return floatval($val);
}

if(!function_exists('str_getcsv')) {
    function str_getcsv($input, $delimiter = ',', $enclosure = '"') {

        if( ! preg_match("/[$enclosure]/", $input) ) {
          return (array)preg_replace(array("/^\\s*/", "/\\s*$/"), '', explode($delimiter, $input));
        }

        $token = "##"; $token2 = "::";
        //alternate tokens "\034\034", "\035\035", "%%";
        $t1 = preg_replace(array("/\\\[$enclosure]/", "/$enclosure{2}/",
             "/[$enclosure]\\s*[$delimiter]\\s*[$enclosure]\\s*/", "/\\s*[$enclosure]\\s*/"),
             array($token2, $token2, $token, $token), trim(trim(trim($input), $enclosure)));

        $a = explode($token, $t1);
        foreach($a as $k=>$v) {
            if ( preg_match("/^{$delimiter}/", $v) || preg_match("/{$delimiter}$/", $v) ) {
                $a[$k] = trim($v, $delimiter); $a[$k] = preg_replace("/$delimiter/", "$token", $a[$k]); }
        }
        $a = explode($token, implode($token, $a));
        return (array)preg_replace(array("/^\\s/", "/\\s$/", "/$token2/"), array('', '', $enclosure), $a);

    }
}

/*
 * Edit this function and you will die roaring.
*/
function parse_args($args) {

    if(is_string($args)){
        // Cleanup characeters to place them back in args.
        $args = str_replace(array('=', "\'", '\"'), array('= ', '&#39;', '&#34;'), $args);
        $args = str_getcsv($args, ' ', '"');
        $tmp = array();
        foreach($args as $arg){
            if(!empty($arg) && $arg != "&#39;" && $arg != "=" && $arg != " "){
                $tmp[] = str_replace(array('= ', '&#39;', '&#34;'), array('=', "'", '"'), trim($arg));
            }
        }
        $args = $tmp;
    }

    $out = array();
    $args_size = count($args);
    for($i = 0; $i < $args_size; $i++){
        $value = false;
        // command --abc
        if( substr($args[$i], 0, 2) == '--' ){
            $key = rtrim(substr($args[$i], 2), '=');
            $out[$key] = true;
        // command -a
        }else if( substr($args[$i], 0, 1) == '-' ){
            $key = rtrim(substr($args[$i], 1), '=');
            $opt = str_split($key);
            $opt_size = count($opt);
            if( $opt_size > 1){
                // "command -c d e" would be "c=d e")
                for ($n=0; $n < $opt_size; $n++) {
                    $key = $opt[$n];
                    $out[$key] = true;
                }
            }        
        }else{
            $value = $args[$i];
        }

        // Asign key to output array
        if( isset($key) ){
            if( isset($out[$key]) ){
                if( is_bool($out[$key]) ){
                    $out[$key] = $value;    
                }else{
                    // You could add type checking here but ftw
                    $out[$key] = trim($out[$key].' '.$value);
                }
            }else{
                $out[$key] = $value;
            }
        }else if($value){
            $out[$value] = true;
        }
    }
    return $out;
}

# Return
if(isset($_POST['cmd'])){
    $token = (isset($_POST['cmd_token'])) ? strip_tags(trim($_POST['cmd_token'])) : false;
    $query = (isset($_POST['cmd_query'])) ? strip_tags(trim($_POST['cmd_query'])) : '';
    $in    = parse_args(strip_tags(trim($query.' '.$_POST['cmd'])));
    $out   = array('type' => 'print', 'out' => '' );

    if( isset($_GET['start']) ){
        $out['out'] = '<p>Hi, welcome to the Ptty response object tutorial. Type <code>quit</code> or <code>exit</code> to leave.'
        .'<p>Now would be a good moment to start up your console to see responses like this one:</p>'
        .'<pre><code>{ "type":"print", "out":"bla bla", query":"--name" }</code></pre>'
        .'<p>This means:<br>a) Use <code>type</code> to specify your kind of output.<br>'
        .'b) <code>out</code> has the text to be passed on to the terminal.<br>'
        .'c) Return a <code>query</code> with value "--name" in the next request.</p>'
        .'<p>Ok lets get started. What\'s your name?</p>';
        $out['query'] = '--name';
        
    }else if( isset($_GET['end']) ){
        $out['out'] = 'You have ended the prompt chain by typing <code>exit</code> or <code>quit</code>.';
        
    }else if( in_array('exit', array_values($in), true) || in_array('quit', array_values($in), true) ){
        $out['out']   = 'Exit signal detected. Leaving tutorial.';
        $out['exit']  = true;
        
    }else if( isset($in['exit_true']) ){
        $out['out'] = '<p>Bye now. ^_^</p>';
        $out['exit'] = true;
        
    }else if( isset($in['exit']) && $token ){
        $out['out'] = '<p>Lastly we have the <code>exit</code> property that will terminate a subroutine on demand.</p>'
        .'<p>By default the "exit" and "quit" keywords will terminate a subroutine, but the <code>exit</code> property '
        .'requires no user input to quit.</p>'
        .'<p>Type "exit" or "quit" if you would like to exit by your own free will, '
        .'or just press enter and the tutorial will exit by itself.</p>';
        $out['query']    = '--exit_true';

    }else if( isset($in['tokens']) && $token ){
    // Start session with token.
        $out['out']   = '<p>If you look at the last POST you should see:</p>'
        .'<pre><code>cmd_token : "'.$token.'"</code></pre>'
        .'<p>Ptty creates a token when you use the <code>type : \'prompt\'</code> property value.</p>'
        .'<p>You can also use the <code>token</code> property combined with <code>type : \'prompt\'</code> '
        .'for a server side generated token.</p>'
        .'<p>This token will persist until changed or its value is set to false in the reply.</p>'
        .'<p>( Please press enter to continue... )</p>';
        $out['query'] = '--exit';
        $out['token'] = $token;
        $out['type'] = 'prompt';

    }else if( isset($in['name']) && isset($in['age']) && isset($in['secret']) ){
    // Love
        if($in['secret'] == '--secret'){
            $out['out']      = '<p>Common, spill the beans...</p>';
            $out['ps']       = 'Type something secret';
            $out['type'] = 'password';
            $out['query']    = '--name "'.$in['name'].'" --age '.$in['age'].' --secret ';
            $out['quiet']    = 'password';
        }else{
            $in['secret'] = (count($in) >= 4) ? $in['secret'].' '.implode(' ', array_slice(array_keys($in), 3)) : $in['secret'];    
            $out['out']   = '<p>Look Ma! A password input! That was done using the <code>type : password</code> response.</p>'
            .'<p>Also, I didn\'t output your secret in the line above by using the <code>quiet : password</code> '
            .'response property. It\'s a good idea to use those two together.</p>'
            .'<p>Now the response ends in:</p>'
            .'<pre><code>{ ... "query":"--tokens", "quiet":"password" "ps":null}</code></pre>'
            .'<p>Note how the <code>quiet</code> property is used "on response" unlike the <code>query</code> property.'
            .' Also the <code>ps</code> property has been set to null to reset the promp name to its original value.</p>'
            .'<p>( Please press enter to continue... )</p>';
            $out['type']  = 'prompt';
            $out['query'] = '--tokens';
            $out['quiet'] = 'password';
            $out['ps']    = null;
        }

    }else if( isset($in['name']) && isset($in['age']) ){
    // Age
        if(!is_numeric($in['age'])){
            if(count($in) >= 3){
                $numbers = array_slice(array_keys($in), 2);
                $in['age'] = strtolower($in['age'].' '.implode(' ',$numbers));
            }
            $age = wordsToNumber($in['age']);
            if($age){
                $out['out'] = '<p>Ok, if you say you\'re '.$in['age'].' then you\'re '.$age.'.</p>';
                $in['age']  = $age;
            }else{
                $in['age'] = rand(15,65);
                $out['out']   = '<p>That\'s a weird age, Ok I\'ll guess then.<br/>';
                $out['out']  .= 'You are probably around '.$in['age'].'.</p>';
            }
        }else{
            $out['out'] = '<p>Ok, so you are '.$in['age'].'.</p>';
        }
        $out['out']      .= '<p>Now our response ending would be:</p>';
        $out['out']      .= '<pre><code>{ ... "query":"--name \''.$in['name'].'\' --age \''.$in['age'].'\' --secret " }</code></pre>';
        $out['out']      .= '<p>Next write something personal...</br>Don\' worry, I promise not to output your secret!</p>';
        $out['ps']        = 'Type something secret';
        $out['query']     = '--name "'.$in['name'].'" --age '.$in['age'].' --secret ';
        $out['type']  = 'password';

    }else if( isset($in['name']) ){
    // Name
        if($in['name'] == '--name' || $in['name'] == ''){
            $names = array('Alice', 'Bob', 'Charlie', 'Dan', 'Eve', 'Frank', 'Mallory', 'Oscar', 'Peggy', 'Sybil', 'Trent', 'Wendy');
            shuffle($names);
            $in['name']  = $names[0];
            $out['out']  = '<p>No name? Fine, I\'ll call you '.$in['name'].'.<br/>';
        }else{
            if(count($in) >= 2){
                $last_names = array_keys($in);
                array_shift($last_names);
                $in['name'] = $in['name'].' '.implode(' ',$last_names);
            }
            $out['out']  = '<p>Hi '.$in['name'].', <br>';
        }
        $out['out'] .= 'See how this response ended in:</p>'
        .'<pre><code>{ ... "query":"--name \''.$in['name'].'\' --age ", "ps":"How old are you?"}</code></pre>'
        .'<p>The <code>query</code> property is saving the data.</p>'
        .'<p>The <code>ps</code> property is a better way to ask for input.</p>'
        .'Lets do it again:</p>';
        $out['query'] = '--name "'.$in['name'].'" --age ';
        $out['ps']    = 'How old are you?';

    }else{

        $out['out'] .= 'How\'d you get here?!';
        $out['exit'] = true;
    }

    # Output
    header('Content-Type: application/json');
    echo(json_encode($out));
}
die();

<?php

$jokes = array(
    'A dog sees a blue whale in the forest, the dog asks: "Don\'t you live in the ocean?". The blue whale replies: "Yes".',
    'I\'d tell you a UDP joke, but you wouldn\'t get it.',
    'An atom walks out of a bar, and says "I\'ve lost an electron!". His friend asks: "Are you sure?", and the atom replies: "I\'m positive!".',
    'A dyslexic guy walks in to a bra...',
);

$wisdoms = array(
    'Don\'t take yourself seriously, no one else does.',
    'A light heart lives long.',
    'If you\'re not paying for a product, you are the product.',
    'All that is spoken, is spoken by someone.',
);

if(isset($_POST['cmd'])){
    $in = explode(' ', strip_tags($_POST['cmd']));
    $out = array('cmd_type' => 'print', 'out' => '' );

    if(isset($in[0])){
        if($in[0] == 'fortune'){
            if(isset($in[1])){
                if($in[1] == '-joke'){
                    $fortune = $jokes;
                }elseif($in[1] == '-wisdom'){
                    $fortune = $wisdoms;
                }else{
                    $fortune = array('invalid option: "'.$in[1].'".');    
                }
            }else{
                $fortune = array_merge($jokes, $wisdoms);
            }
        }
        shuffle($fortune);
        $out['out'] = $fortune[0];
    }

    # Output
    header('Content-Type: application/json');
    echo(json_encode($out));
}
die();

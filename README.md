# ptty.jquery.js

[Ptty](http://goto.pachanka.org/ptty/docs) is a jQuery plugin that creates an expansible terminal emulator. It is small, it is fast and it is fully customizable by adding commands and callbacks.

* Current version 0.0.5
* Size 12 Kb (minified)

## Features

Ptty comes with a set of little helpers so to be as light and scalable as possible, It can:

* Expand on demand using the <code>$ptty.register()</code> method.
* Build auto-documenting commands using the help feature.
* Add callbacks and callbefores.
* Texts can be translated through the settings and changed at any time.
* Fully CSS themable.
* Its not perfect but its readable.
* Commands history, help and clear are built in.

## Usage

To start Ptty simply do the following:
    
    $(document).ready(function(){
        var $ptty = $('#terminal').ptty();
    });

Or you can use [settings](http://goto.pachanka.org/ptty/docs#settings):
    
    $(document).ready(function(){
	    var $ptty = $('#terminal').ptty({
	        ps     : '$',
	        theme  : 'boring',
	        i18n   : {
                welcome: 'Welcome to the matrix.'
            }
	    });
	});

After initiation you can use methods like so:

    $ptty.register('command', { 
        name : 'hello', 
        method : function(cmd){
            cmd.out = 'Hello World!';
            return cmd;
        } 
    });

    // Or:
    $ptty.echo('Hello World!');


## Demo & Docs

Please see the [online documentation](http://goto.pachanka.org/ptty/docs) to learn about the plugin options and response structure or look at the [Demo](http://goto.pachanka.org/ptty/) for a full screen example.

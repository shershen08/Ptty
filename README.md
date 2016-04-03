# Ptty.jquery.js

[Ptty](http://goto.pachanka.org/ptty/) is a jQuery plugin that creates an expansible terminal emulator. It is small, it is fast and it is fully customizable by adding commands and callbacks.

* Current version 0.0.4
* Size 11.6 Kb (minified)

## Features

Ptty comes with a set of little helpers so to be as light and scalable as possible, It can:

* Expand on demand using the <code>$.register_command()</code> method.
* It auto-documents all commands and usage by requiring command descriptions and usage.
* Add callbacks and callbefores with the <code>$.register_callback()</code> and <code>$.register\_callbefore()</code> command.
* Sub-routines are available.
* Command refreshing by using the <code>$.flush_commands()</code> method.
* Fully CSS themable.
* Upload files via AJAX.
* Its not perfect but its readable.
* Command History, help and clear commands.

## Usage

To start Ptty simply do the following:

    $(document).ready(function(){
        $('#terminal').Ptty();
    });

Or you can use [options](http://goto.pachanka.org/ptty/#options):
    
    $(document).ready(function(){
	    $('#terminal').Ptty({
	        url    : 'ajax/',
	        ps     : '',
	        theme  : 'boring',
	        welcome: 'Welcome to the matrix.'
	    });
	});
## Demo & Docs

Please see the [online documentation](http://goto.pachanka.org/ptty/) to learn about the plugin options and response structure or look at the [Demo](http://goto.pachanka.org/ptty/demo) for a full screen example.

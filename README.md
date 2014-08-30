# Ptty.jquery.js

[Ptty](http://code.patxipierce.com/jquery-plugin/ptty/) is a jQuery plugin that creates an expansible terminal emulator. It is small, it is fast and it is fully customizable by adding commands and callbacks.

* Current version 0.0.1
* Size 8.9 Kb (minified)
* [Download](http://code.patxipierce.com/jquery-plugin/ptty/v/0.1/Ptty.jquery.tar.gz)(12.1 kb)

## Features

It can:

* Expand on demmand using the <code>$.register_command()</code> method.
* It auto-documents all commands and usage by requiring command descriptions and usage.
* Add callbacks (including other jQuery plugins) with the <code>$.register_callback()</code> command.
* Sub-routines are available.
* Command refreshing by using the <code>$.flush_commands()</code> method.
* Fully CSS themable.


## Usage

To start Ptty simply do the following:

    $(document).ready(function(){
        $('#terminal').Ptty();
    });

Or you can use [options](http://code.patxipierce.com/jquery-plugin/ptty/#options):
    
    $(document).ready(function(){
	    $('#terminal').Ptty({
	        // Default ajax URL (can be relative or absolute).
	        url    : 'ajax/',
	        // Set the PS to an empty string and change the defaults to use a custom css theme.
	        ps     : '',
	        theme  : 'boring',
	        welcome: 'Welcome to the matrix.'
	    });
	});
## Demo & Docs

Please see the [Ptty online documentation](http://code.patxipierce.com/jquery-plugin/ptty/) or the [Demo](http://code.patxipierce.com/jquery-plugin/ptty/v/0.1/example.html).


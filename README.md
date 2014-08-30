# Ptty.jquery.js

[Ptty](http://code.patxipierce.com/jquery-plugin/ptty/) is a jQuery plugin that creates an expansible terminal emulator. It is small, it is fast and it is fully customizable by adding commands and callbacks.

* Current version 0.0.1
* Size 8.9 Kb (minified)

## Features

Ptty comes with a set of little helpers so to be as light and scalable as possible, It can:

* Expand on demmand using the <code>$.register_command()</code> method.
* It auto-documents all commands and usage by requiring command descriptions and usage.
* Add callbacks (including other jQuery plugins) with the <code>$.register_callback()</code> command.
* Sub-routines are available.
* Command refreshing by using the <code>$.flush_commands()</code> method.
* Fully CSS themable.
* Its not perfect but its readable.
* Command History, help and clear commands.


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

Please see the [online documentation](http://code.patxipierce.com/jquery-plugin/ptty/) or the [Demo](http://code.patxipierce.com/jquery-plugin/ptty/v/0.1/example.html).

## Wishlist

**To Do for version 0.1.0:**

* Refactor code and clean up unnecessary statements
* Add option for disabled="disabled" to input while ajax takes place.
* Try contenteditable="true" instead of input="text"
* Add upload type option. Similar to password.
* Make a "scroll to command" response property that scrolls to the last input instead of the bottom.

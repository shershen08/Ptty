# Ptty.jquery.js
<br>
[Ptty](http://code.patxipierce.com/jquery-plugin/ptty/) is a jQuery plugin that creates an expansible terminal emulator.

## Features
<br>
It can:

* Expand on demmand using the <code>$.register_command()</code> method.
* It auto-documents all commands and usage by requiring command descriptions and usage.
* Add callbacks (including other jQuery plugins) with the <code>$.register_callback()</code> command.
* Sub-routines are available.
* Command refreshing by using the <code>$.flush_commands()</code> method.
* Fully CSS themable.


## Usage
<br>
To start Ptty simply do the following:

    $(document).ready(function(){
        $('#terminal').Ptty();
    });
    
## Demo & Docs
<br>
Please see the [Ptty online documentation](http://code.patxipierce.com/jquery-plugin/ptty/) or the [Demo](http://code.patxipierce.com/jquery-plugin/ptty/v/0.1/example.html).


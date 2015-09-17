<?php


if(isset($_GET['kitty_id']) && is_numeric($_GET['kitty_id'])){

// Outputs the raw file.
    if($_GET['kitty_id'] === 'test'){
        $kitty_file = '../img/kitties/test/ktytest.png';
    }else{
        $kitty_file = '../img/kitties/'.$_GET['kitty_id'].'.png';   
    }
    
    if(file_exists($kitty_file)){
        header('Content-Type: image/png');
        header('Expires: 0');
        header('Content-Length: '.filesize($kitty_file));
        header('X-Content-Type-Options: nosniff');
        readfile($kitty_file);
    }
    die();

}else if(isset($_POST['cmd'])){
// Commands
    $in = explode(' ', strip_tags($_POST['cmd']));
    $out = array('type' => 'print', 'out' => '' );
    if(isset($_GET['get'])){
    // Show a kitty!
        if(isset($in[1]) && is_numeric($in[1])){
            $kitty_id = (strlen($in[1]) == 1) ? str_pad($in[1], 2, '0', STR_PAD_LEFT) : $in[1];
            if(file_exists('../img/kitties/'.$kitty_id.'.png')){
                $schema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
                $url = $schema.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?kitty_id='.$kitty_id.'&'.md5(time());
                if(isset($in[2])){
                // Check for callbacks
                    switch($in[2]){
                        case 'redirect':
                            $out['callback'] = 'redirect';
                            $out['out'] = '<a href="'.$url.'">Going towards kitty!</a> ...';
                            $out['url'] = $url;
                            break;
                        case 'dialog':
                            $out['callback'] = 'dialog';
                            $out['out']      = 'Showing kitty in dialog.';
                            $out['html'] = '<img alt="kitty '.$kitty_id.'" src="'.$url.'" />'
                            .'<h2>Kitty no.'.$kitty_id.'</h2><p>Oh! such a pretty kitty!!</p>';
                            break;
                        default :
                            $out['callback'] = 'print';
                            $out['out'] = '<p>Unrecognized callback, but here is a kitty:</p>'
                            .'<img alt="kitty '.$kitty_id.'" src="'.$url.'" />';
                    }
                }else{
                    $out['out'] = '<img alt="kitty '.$kitty_id.'" src="'.$url.'" />';
                }
            }else{
                $out['out'] = 'That kitty ID dosen\'t exist.<br /><i>Hint</i>: Try a number from 01 to 10, or type "ktyreset" to bring all the kittens back.';  
            }
        }else{
            $out['out'] = 'ID must be a number.<br>Usage: ktyget [kitty ID]';
        }
    }else if(isset($_GET['put'])){
    // Put a kitty in the API
        $kittys = glob('../img/kitties/*.png');
        if(count($kittys) >= 10){
            $out['out']  = 'There are too many kitties!!</br>';
            $out['out'] .= 'Please delete one first using the ktydel command.';
        }else{
            $schema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
            $url = $schema.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?kitty_id=test&'.md5(time());
            $help = '<p>Try using <a href="'.$url.'" terget="_blank">this image url</a> for example</p>';

            if( isset($in[1]) && filter_var($in[1], FILTER_VALIDATE_URL) !== false ){

                // Save the godamn image.
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $file_contents = file_get_contents($in[1]);
                $mime_type = $finfo->buffer($file_contents);

                // create and change no non-executable
                $tmp = tempnam(sys_get_temp_dir(), "tmp_image"); 

                if(file_put_contents($tmp, $file_contents)){
                    
                    $img_info = getimagesize($tmp);

                    if(filesize($tmp) > 1024 && isset($img_info['mime']) 
                        && $img_info['mime'] === 'image/png' && $mime_type === 'image/png'){
                        
                        $thumb_width = 200;
                        $thumb_height = 200;
                        $image = imagecreatefrompng($tmp);
                        $width = imagesx($image);
                        $height = imagesy($image);
                        $thumb = imagecreatetruecolor( $thumb_width, $thumb_height );
                        // Keep $image transparency (if any)
                        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);

                        //resize if image is bigger than 200
                        if($img_info[0] >= 200 || $img_info[1] >= 200){

                            $original_aspect = $width / $height;
                            $thumb_aspect = $thumb_width / $thumb_height;

                            if ( $original_aspect >= $thumb_aspect ){
                               // If image is wider than thumbnail (in aspect ratio sense)
                               $new_height = $thumb_height;
                               $new_width = $width / ($height / $thumb_height);
                            }else{
                               // If the thumbnail is wider than the image
                               $new_width = $thumb_width;
                               $new_height = $height / ($width / $thumb_width);
                            }
                            
                            // Resize and crop
                            imagecopyresampled($thumb,
                                               $image,
                                               0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
                                               0 - ($new_height - $thumb_height) / 2, // Center the image vertically
                                               0, 0,
                                               $new_width, $new_height,
                                               $width, $height);
                        }else{
                            // Save as 200x200.
                            imagecopyresampled($thumb,
                                               $image,
                                               0, 0,
                                               0, 0,
                                               $thumb_width, $thumb_height,
                                               $width, $height);
                        }
                        //Convert to gray-scale
                        imagefilter($thumb, IMG_FILTER_GRAYSCALE);
                        // Find Available file slot
                        foreach (range(1, 10) as $num) {
                            $kitty_id = str_pad($num, 2, '0', STR_PAD_LEFT);
                            if(!file_exists('../img/kitties/'.$kitty_id.'.png')){
                                break;
                            }
                        }
                        // Copy tmp file to kitties!
                        $new_kitty_file = '../img/kitties/'.$kitty_id.'.png';
                        if(imagepng($thumb, $new_kitty_file, 8) && chmod($new_kitty_file, 0644)){
                            $url = $schema.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?kitty_id='.$kitty_id.'&'.md5(time());
                            $out['out']  = 'URL is vallid. Kitty added with ID '.$kitty_id.'.';
                            $out['out'] .= '</br><img src="'.$url.'" alt="Kitty number '.$kitty_id.'"/>';
                        }else{
                            $out['out'] = 'Error saving kitty! Nooooo!';
                        }
                        // Cleanup
                        imagedestroy($image);
                        imagedestroy($thumb);

                    }else{
                        $out['out']  = '<p>Only kitties URLs (smaller than 1Mb and in PNG format) please.</p>';
                        $out['out'] .= $help;
                    }
                }else{
                    $out['out'] = '<p>Couldn\'t save kitty :-(. Use a different URL.</p>';
                    $out['out'] .= $help;
                }
                // Cleanup tmp file.
                if(file_exists($tmp)){
                    unlink($tmp);    
                }
            }else{
                $out['out'] = '<p>Invalid URL.<br>Usage: ktyput [URL to image]</p>';
                $out['out'] .= $help;
            }   
        }
        
    }else if(isset($_GET['del'])){
    // Remove a kitty
        if(isset($in[1]) && is_numeric($in[1])){
            $kitty_id = (strlen($in[1]) == 1) ? str_pad($in[1], 2, '0', STR_PAD_LEFT) : $in[1];
            if(file_exists('../img/kitties/'.$kitty_id.'.png')){
                if(unlink('../img/kitties/'.$kitty_id.'.png')){
                    $out['out'] = 'Kitty deleted, you monster!';
                }else{
                    $out['out'] = 'Error deleting kitty.';
                }
            }else{
                $out['out'] = 'That kitty ID dosen\'t exist.';  
            }
        }else{
            $out['out'] = 'ID must be a number.<br>Usage: ktydel [kitty ID]';
        }
    }else if(isset($_GET['reset'])){

        foreach (range(1, 10) as $num) {
            $kitty_id = str_pad($num, 2, '0', STR_PAD_LEFT);
            $old_kitty = '../img/kitties/'.$kitty_id.'.png';
            $new_kitty = '../img/kitties/reset/'.$kitty_id.'.png';
            if( is_file($old_kitty) ){
                unlink($old_kitty);
            }
            if( is_file($new_kitty) ){
                copy($new_kitty, $old_kitty);    
            }
        }      

        $out['out'] = 'Kitties resuscitated from backup. Yay!';
    }
    # Output
    header('Content-Type: application/json');
    echo(json_encode($out));
}
die();


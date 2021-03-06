<?php
//define('PLACEWEB_INCLUDE_PATH', 'include/');
//require(PLACEWEB_INCLUDE_PATH.'vitals.inc.php');
/*
 * jQuery File Upload Plugin PHP Example 5.2.4
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 *
 * Hacked and refined to handle video conversion by Michael Moncada
 */

//error_reporting(E_ALL | E_STRICT);
set_time_limit(0);
ini_set('memory_limit', '512M');
class UploadHandler
{
    private $options;
    
    function __construct($options=null) {
        global $PLACEWEB_CONFIG;

        $this->options = array(
            'script_url' => '/ajax/uploadfile/',
            'upload_dir' => $PLACEWEB_CONFIG['uploadDir'],
            'upload_url' => 'http://' . $_SERVER['HTTP_HOST'] . $PLACEWEB_CONFIG['uploadWebDir'] . "",
            'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            'discard_aborted_uploads' => true,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images. You can also add additional versions with
                // their own upload directories:
                /*
                'large' => array(
                    'upload_dir' => dirname(__FILE__).'/files/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
                    'max_width' => 1920,
                    'max_height' => 1200
                ),
                */
                'thumbnail' => array(
                    'upload_dir' => $PLACEWEB_CONFIG['uploadDir'] . "thumbnails/",
                    'upload_url' => 'http://' . $_SERVER['HTTP_HOST'] . $PLACEWEB_CONFIG['uploadWebDir'] . "thumbnails/",
                    'max_width' => 80,
                    'max_height' => 80
                )
            )
        );
	
	
        
	if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }
    
    private function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].rawurlencode($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url']
                        .rawurlencode($file->name);
                }
            }
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name);
            $file->delete_type = 'DELETE';
            return $file;
        }
        return null;
    }
    
    private function get_file_objects() {
        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            scandir($this->options['upload_dir'])
        )));
    }

    private function create_scaled_image($file_name, $options) {
	@mkdir( $options['upload_dir'], 0755, true); 
        $file_path = $this->options['upload_dir'].$file_name;
        $new_file_path = $options['upload_dir'].$file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale > 1) {
            $scale = 1;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                break;
            case 'gif':
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                break;
            case 'png':
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                break;
            default:
                $src_img = $image_method = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }
    
    private function has_error($uploaded_file, $file, $error) {
        if ($error) {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }
    
    private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
        $file = new stdClass();
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file->name = preg_replace("/[^\w\._\-]/", '_', trim(basename(stripslashes($name)), ".\x00..\x20"));
        $file->size = intval($size);
        $file->type = $type;
        $error = $this->has_error($uploaded_file, $file, $error);
        if (!$error && $file->name) {
            $file_path = $this->options['upload_dir'] . $file->name;
            $append_file = is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
                $file->url = $this->options['upload_url'].rawurlencode($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
                    if ($this->create_scaled_image($file->name, $options)) {
                        $file->{$version.'_url'} = $options['upload_url']
                            .rawurlencode($file->name);
                    }
                }
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $file->size = $file_size;
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name);
            $file->delete_type = 'DELETE';
            
            
            // encode the file if necessary
            if ($file_path)
            {
                $encodedFileInfo = $this->encodeFile($this->options['upload_dir'], $file->name);
                
                $newFileName = $encodedFileInfo[0]; 
                $file->error = $encodedFileInfo[1];
                $file->is_video = $this->isVideoFile($file->name);
                $file->name = $newFileName;
                $file->url = $this->options['upload_url'].rawurlencode($newFileName);
            }   
        } else {
            $file->error = $error;
        }
        return $file;
    }
    
    public function get() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null; 
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        //header('Content-type: application/json');
        echo json_encode($info);
    }
    
    public function post() {
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : array(
                'tmp_name' => null,
                'name' => null,
                'size' => null,
                'type' => null,
                'error' => null
            );
        $info = array();
        if (is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } else {
            $info[] = $this->handle_file_upload(
                $upload['tmp_name'],
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                $upload['error']
            );
        }
        //header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            //header('Content-type: application/json');
        } else {
            //header('Content-type: text/plain');
        }
	//echo 'aaaaaaa';
        echo json_encode($info);
    }
    
    public function delete() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'].$file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
       // header('Content-type: application/json');
        echo json_encode($success);
    }
    
    function isVideoFile($fileName)
    {
        $filenameTokens = explode(".", $fileName);
        $filenameExtension = strtolower($filenameTokens[count($filenameTokens) - 1]);
        $isVideo = false;
    
        switch ($filenameExtension)
        {
            case 'avi':
            case 'flv':
            case 'mov':
            case 'mpg':
            case 'mpeg':
            case 'mp4':
            case 'm4v':
            case 'wmv':
            case 'vob':
            case 'asx':
            case 'asf':
            case 'rm':
            case 'swf':
            case '3g2':
            case '3gp':
            case 'divx':
            case 'ogv':
                $isVideo = true;
                break;
            default:
                break;
        }
    
        return $isVideo;
    }
    
    function encodeFile($filePath, $fileName)
    {
	global $PLACEWEB_CONFIG;

        $error = null;
        
        if (! $this->isVideoFile($fileName)) return array($fileName, $error);
    
        $tkns = explode(".", $fileName);
        unset($tkns[count($tkns) - 1]);
        $newFileName = implode(".", $tkns) . "_encoded.mp4";
        
        $outputFileName = $filePath . $newFileName;
        $srcFile = $filePath . $fileName;
        $output = array();
        $processReturnResult = '';
        $result = exec($PLACEWEB_CONFIG['ffmpegPath'] . " -i $srcFile -s 320x240 -r 30000/1001 -b 200k -bt 240k -vcodec libx264 -coder 0 -bf 0 -flags2 -wpred-dct8x8 -level 13 -maxrate 768k -bufsize 3M -acodec libfaac -ac 2 -ar 48000 -ab 192k -y $outputFileName", &$output, &$processReturnResult);
        //echo "/usr/local/bin/ffmpeg -i $srcFile -s 320x240 -r 30000/1001 -b 200k -bt 240k -vcodec libx264 -coder 0 -bf 0 -flags2 -wpred-dct8x8 -level 13 -maxrate 768k -bufsize 3M -acodec libfaac -ac 2 -ar 48000 -ab 192k -y $outputFileName";
        //ffmpeg -i  example.mpg -aspect 4:3 -ab 128kb  -b 1200kb -ar 44100 -vcodec mpeg1video -acodec mp2 -s 320x240 -y converted.mpg
       
        if (! file_exists($outputFileName))
        {
            $error = "Video/Audio Codec not supported";
            return array($fileName, $error);
        }
        elseif (filesize($outputFileName) == 0)
        {
            $error = "Video/Audio Codec not supported";
            @unlink($outputFileName);
            return array($fileName, $error);
        }
        else
        {
            @unlink($srcFile);
        }
        
        return  array($newFileName, $error);
    }
   
}
/*
$upload_handler = new UploadHandler();

header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        $upload_handler->post();
        break;
    case 'DELETE':
        $upload_handler->delete();
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}
*/

?>

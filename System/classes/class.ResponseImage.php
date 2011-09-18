<?php

class ResponseImage extends Response
{
    function __construct($url, $extension)
    {
        $extension = strtolower($extension);
        parent::__construct('');
        $this->AddHeader('Content-Type', 'image/'.$extension);
        ob_start();
        $im = null;
        if($extension == "jpg")
        {
            $im = imagecreatefromjpeg($url);
            imagejpeg($im);
        }
        else
        {
            $im = call_user_func('imagecreatefrom'.$extension, $url);
            call_user_func('image'.$extension, $im);
        }
        imagedestroy($im);
        $this->SetResponseContent(ob_get_clean());
    }
}
?>

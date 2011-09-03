<?

function getSource($filename, $err_handler = null)
    {
        if(file_exists($filename))
        {
            require($filename);
            return true;
        }
        else
        {
            if($err_handler !== null)
            {
                return call_user_func($err_handler, $filename);
            }
        }
        return false;
}

function renderTemplate($template, $data)
{
    return CommonUtils::RenderToHtml($template, $data);
}

function objToArray($obj)
{
    return CommonUtils::ObjectToArray($obj);
}

function redirect($url, $lag)
{
    CommonUtils::RedirectTo($url, $lag);
}

function dump($var)
{
    echo CommonUtils::PrintR($var);
}

function sdump($var)
{
    ob_start();
    dump($var);
    return ob_get_clean();
}

function writeLn($text)
{
    print $text."\n";
}
/**
 *
 * @param type $template
 * @param type $args
 * @return string response as string 
 */
function render_to_response_str($template, $args)
{
    $loader = new Twig_Loader_Filesystem(Application::$Params['template']);
    $twig = new Twig_Environment($loader, array(
      //'cache' => '/path/to/compilation_cache',
    ));
    $templateRenderer = $twig->loadTemplate($template);
    return $templateRenderer->render($args);
}


function render_to_response($template, $args)
{
    return new ApiResponse(render_to_response_str($template, $args));
}

function api_response($args, $tpl = null)
{
    if($tpl === null)
    {
        $dbg = debug_backtrace();
        dump($dbg);
        return new ApiResponse(render_to_response_str($dbg[1]['function'].'.xml', array_merge(array('IsError' => 0) ,$args)));
    }
    else
    {
        return new ApiResponse(render_to_response_str($tpl.'.xml', array_merge(array('IsError' => 0) ,$args)));
    }
}

function api_error($code, $message, $args = array())
{
    return render_to_response('error.xml', array_merge(array('Code' => $code, 'Message' => $message), $args));
}

function encrypt($str, $key)
{
    $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, substr(md5($key), 0, 24), $iv);
    $encrypted_data = mcrypt_generic($td, $str);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return base64_encode($encrypted_data);
}

function decrypt($str, $key)
{
    $str = base64_decode($str);
    $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td, substr(md5($key), 0, 24), $iv);
    $decrypted_data = mdecrypt_generic($td, $str);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $decrypted_data;
}

function toXml($what)
{
    $w = new XMLWriter();
    $w->openMemory(true);
    $w->setIndent(true);
    if(is_object($what))
    {
        $w->startElement(get_class($what));
        $fields = get_object_vars($what);
        foreach($fields as $k => $v)
        {
            $w->writeElement($k, $v);
        }
        $w->endElement();
    }
    else if(is_array($what))
    {
        $w->startElement("Array");
        foreach($what as $k => $v)
        {
            $w->writeElement($k, $v);
        }
        $w->endElement();
    }
    return $w->outputMemory();
}

?>

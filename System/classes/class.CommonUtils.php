<?
#
#   basic functions
#
#   copyright xternalx, 2008 - 2009
#
class CommonUtils
{
    public static function QuoteSql($message)
    {
        return mysql_real_escape_string($message);
    }

    public static function QuoteChars($message)
    {
        return htmlspecialchars($message);
    }
    
    public static function Quote($message)
    {
        $message=$this->QuoteSql($message);
        $message=$this->QuoteChars($message);
        return $message;
    }
    
    public static function StripHtmlTags($str)
    {
        return preg_replace('/<[a-zA-Z\/][^>]*>/i','',$str);
    }
    
    public static function GenerateUID($prefix = '')
    {
            $chars = md5(uniqid(mt_rand(), true));
            $uuid  = substr($chars,0,8) . '-';
            $uuid .= substr($chars,8,4) . '-';
            $uuid .= substr($chars,12,4) . '-';
            $uuid .= substr($chars,16,4) . '-';
            $uuid .= substr($chars,20,12);
            return $prefix . $uuid;
	}
    
    public static function FindFiles($dir,$mask)
    {
        $r=opendir($dir);
        $i=0;
        while (false != ($file = readdir($r)))
        { 
            if($file!="." && $file!=".." && ereg(strtolower($mask),strtolower($file)))
            {
                $files[$i]['name']=$file;
                $files[$i]['size']=filesize($dir.$file);
                $files[$i]['date_created'] = filemtime($dir.$file);
                $files[$i]['count']=$i+1;
                $i++;
            }
        }
        closedir($r);
        return $files;
    }
    
    public static function AddPrefix($arr, $pfx)
    {
        if(is_array($arr) && count($arr)>0)
        {
            $new_a = array();
            foreach($arr as $k=> $v)
            {
                $new_a[$pfx.$k] = $v;
            }
            return $new_a;
        }
        return $arr;
    }
    
    public static function DelPrefix($arr, $pfx)
    {
        if(is_array($arr) && count($arr)>0)
        {
            $new_a = array();
            $len = 0;
            if(is_numeric($pfx))
            {
                $len = $pfx;
            }
            else
            {
                $len = strlen($pfx);
            }
            
            foreach($arr as $k=> $v)
            {
                $new_a[substr($k,$len)] = $v;
            }

            return $new_a;
        }
        return $arr;

    }

    public static function AddPostfix($arr, $pfx)
    {
        if(is_array($arr) && count($arr)>0)
        {
            $new_a = array();
            foreach($arr as $k=> $v)
            {
                $new_a[$k.$pfx] = $v;
            }
            return $new_a;
        }
        return $arr;
    }
    
    public static function DelPostfix($arr, $pfx)
    {
        if(is_array($arr) && count($arr)>0)
        {
            $new_a = array();
            foreach($arr as $k=> $v)
            {
                $new_a[$k.$pfx] = $v;
            }
            return $new_a;
        }
        return $arr;
    }
    
    public static function ArrayDecorateKeys($array, $prefix = "", $postfix = "")
    {
        
        $a = array();
        foreach($array as $k => $v)
        {
            $a[$prefix.$k.$postfix] = $v;
        }
        return $a;
    }
    
    public static function ArrayDecorateValues($array, $prefix = "", $postfix = "")
    {
        $a = array();
        foreach($array as $k => $v)
        {
            $a[$k] = $prefix.$v.$postfix;
        }
        return $a;
    }
    

    public static function StringClone($str, $times)
    {
        $tmp = "";
        for($i=0;$i<$times;$i++)
        {
            $tmp.= $str;
        }
        return $tmp;
    }
    
    public static function MakeObject($object, $array, $prefix = null, $postfix = null)
    {
    }
    
    public static function ObjectFromArray($obj, $params, $prefix = null, $postfix = null)
    {
        if(is_array($params) && count($params)>0)
        {
            if($prefix)
            {
                if(is_string($prefix))
                    $params = self::DelPrefix($params, $prefix);
                else
                    $params = self::DelPrefix($params, $prefix['prefix']);
            }
            
            if($postfix)
            {
                if(is_string($postfix))
                    $params = self::DelPrefix($params, $postfix);
                else
                    $params = self::DelPrefix($params, $postfix['postfix']);
            }
            
            if(is_object($obj))
            {
                $className = get_class($obj);
                foreach($params as $k => $v)
                {
                    if(property_exists($className, $k))
                    {
                        $obj->$k = $v;
                    }
                }
                return $obj;
            }
            else if(is_string($obj))
            {
                
                $o = new $obj();
                
                foreach($params as $k => $v)
                {
                    if(property_exists($obj, $k))
                    {
                        $o->$k = $v;
                    }
                }
            
                return $o;
            }
        }
        return null;
    }
    
    public static function ObjectToArray($obj, $filter_fields = true)
    {
        $a = array();
        $cv = array();
        if($filter_fields)
        {
            $cv = get_class_vars(get_class($obj));
        }
        else
        {
            $cv = get_object_vars($obj);
        }
        foreach($cv as $k => $v)
        {
            $a[$k] = $obj->$k;
        }
        return $a;
    }
    
    public static function Dump($item)
    {
        echo '<pre style="border: 1px dotted red; font-size: 10px">';
            var_dump($item);
        echo '</pre>';
    }

    public static function PrintR($item)
    {
        if(SystemConfig::Get('dDebugMode') === true)
        {
            echo '<pre style="border: 1px dotted red; font-size: 10px; background-color: white; color: red">';
                print_r($item);
            echo '</pre>';
        }
    }
    
    public static function GetWords($delimiter, $string, $count, $start = null)
    {
        $arr = explode($delimiter, $string);
        if(count($arr)>$count)
        {
            $newstr = "";
            if($start == null)
            {
                for($i = 0; $i < $count; $i++)
                {
                    $newstr .= $arr[$i].$delimiter;
                }
                return $newstr;
            }
            else
            {
                for($i = $start; $i < ($count+$start); $i++)
                {
                    $newstr .= $arr[$i].$delimiter;
                }
                return $newstr;
            }
        }
        else
        {
            return $string;
        }
    }
    
    
    public static function UrlToHtmlInputs($type = "hidden", $add_args = null)
    {
        $args = UrlFactory::GetArray();
        $str = array();
        if(is_array($add_args))
        {
            $args = array_merge($args, $add_args);
        }
        foreach($args as $name => $val)
        {
            $str[] = '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$val.'">';
        }
        return implode("\n", $str);
    }
    
    public static function RenderToHtml($template)
    {
        $args = func_get_args();
        $tplData = array();
        foreach($args as $arg)
        {
            if(is_object($arg))
            {
                $arg = self::ObjectToArray($arg, false);
            }
            if(is_array($arg))
            {
                $tplData = array_merge($tplData, $arg);
            }
        }
        return preg_replace(array_keys(CommonUtils::ArrayDecorateKeys($tplData, '/\{', '\}/')), array_values($tplData), $template);
    }
    
    public static function RenderTemplate($template)
    {

        return self::RenderToHtml($template);
    }
    
    public static function RedirectTo($url, $pause = 0)
    {
        echo '
        <script type="text/javascript">
        <!--
        function redirect()
        {
            window.location = "'.$url.'"
        }
        setTimeout("redirect()", '.$pause.')
        //-->
        </script>';
    }
    
    public static function ListToHtmlSelect($list, $keyf, $val)
    {
        $options = "";
        foreach($list as $item)
        {
            if(!is_array($item))
                $item = self::ObjectToArray($item);
            $options .= '<option value="'.$item[$keyf].'">'.self::RenderToHtml($val, $item).'</option>';
        }
        return $options;
    }
    
    public static function MemGetUsage() {
    static $previous = 0;

    $current = memory_get_usage();
    $delta = $current - $previous;
    
    $trace = debug_backtrace();
    
    $result = sprintf('<pre>MemUsage: ' . ($delta ? '%1$+10db' : str_repeat(' ', 10)) . ' %2$6dKiB  line %3$-4d %4$s %5$s</pre>',
        $delta, round($current / 1024), $trace[0]['line'], $trace[0]['file'], array_key_exists(1, $trace) ? $trace[1]['function'].'()' : '');
    
    $previous = $current;
    
    return $result;
}
    
}
?>

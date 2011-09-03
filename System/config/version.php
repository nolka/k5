<?

$cfg['version']="2.0 beta";
#$cfg['resource']="http://userbars.xternalx.com/";
$cfg['resource']="http://localhost/userbars/";

if(Arguments::Get('m') == 'jvars')
{
    echo getJSVars();
}

function getJSVars()
{
    return "
    cfg = {
    'resource': 'http://localhost/userbars/',
        /*'resource': 'http://userbars.xternalx.com/',*/
        'helpdir': '/html/',
        'imgdir': '/images/',
        'user': '".$_SESSION['user']."'
            };
    ";
}

?>

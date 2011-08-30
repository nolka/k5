<?
#
#   MySQL Settings
#

define("DBHOST",        "localhost");
define("DBNAME",        "authorization");
define("DBUSERNAME",    "auth");
define("DBPASSWORD",    "auth");

# стандартный автолоадер
include_once 'autoloader.php';

# автолоадер классов шаблонизатора
include_once SYSROOT.'lib/Twig/Autoloader.php';
Twig_Autoloader::register();

# инициализация класса системных настроек, подключение к БД
SystemConfig::Init(array(
    'db_host' => DBHOST,
    'db_user'=> DBUSERNAME,
    'db_password' => DBPASSWORD,
    'db_db' => DBNAME
));

SystemConfig::Set('dDebugMode', true);
SystemConfig::Set('pFactoryPlugins', SYSROOT.'/plugins/');

foreach(glob(SYSROOT.'/functions/*.php') as $file)
{
    require_once $file;
}

?>
<?php
/**
 * this7 PHP Framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2016-2018 Yan TianZeng<qinuoyun@qq.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ub-7.com
 */
header("Content-Type:text/html;charset=utf-8"); //设置系统的输出字符为utf-8

ob_start(); //启动服务器缓冲

session_start(); //开始服务器session

date_default_timezone_set("PRC"); //设置时区（中国)

defined('DEBUG') or define('DEBUG', true);

/**
 * 设置开始错误信息
 */
if (DEBUG) {
    #打开错误显示
    ini_set("display_errors", "On");
    #开启所有错误提醒
    error_reporting(E_ALL | E_STRICT);
}
#打开错误显示
ini_set("display_errors", "On");
#开启所有错误提醒
error_reporting(E_ALL | E_STRICT);
/**
 * 判断PHP版本是否符合
 */
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    die('This7 需要PHP版本不低于php5.6.0,当前版本' . PHP_VERSION);
}

/**
 * 设置名称及版本号
 */
defined('VERSION') or define('VERSION', "V0.0.1");
defined('FRAMEWORK') or define('FRAMEWORK', "this7");
defined('FRAMEKEY') or define('FRAMEKEY', "www.this7.com");

/**
 * 开始运行时间和内存使用
 */
defined('START_NOW') or define('START_NOW', $_SERVER['REQUEST_TIME']);

defined('START_TIME') or define('START_TIME', microtime(true));

defined('START_MEM') or define('START_MEM', memory_get_usage());

/**
 * 设置物理路径
 */

define('DS', DIRECTORY_SEPARATOR);

define('FRAME_DIR', dirname(dirname(dirname(__FILE__))));

define('ROOT_DIR', dirname(dirname(FRAME_DIR)));

define('VENDOR_DIR', ROOT_DIR . DS . 'vendor');

/**
 * 设置判断字段
 */
defined('IS_CGI') or define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? true : false);

defined('IS_WIN') or define('IS_WIN', strstr(PHP_OS, 'WIN') ? true : false);

defined('IS_GET') or define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');

defined('IS_POST') or define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');

defined('IS_DELETE') or define('IS_DELETE', $_SERVER['REQUEST_METHOD'] == 'DELETE' ?: (isset($_POST['_method']) && $_POST['_method'] == 'DELETE'));

defined('IS_PUT') or define('IS_PUT', $_SERVER['REQUEST_METHOD'] == 'PUT' ?: (isset($_POST['_method']) && $_POST['_method'] == 'PUT'));

defined('IS_AJAX') or define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

defined('IS_WECHAT') or define('IS_WECHAT', isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);

/**
 * 设置地址
 */
define('ROOT', preg_replace("#\/Callback#", "", trim('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/\\')));

defined('URL') or define("URL", rtrim($_SERVER["SCRIPT_NAME"], "/"));

define("HISTORY", isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '');
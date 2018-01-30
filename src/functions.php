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

if (!function_exists('P')) {
    /**
     * 打印输出数据
     * @param  string $name  名称/数据
     * @param  string $value 对应值
     */
    function P($name, $is_unhtml = false) {
        if ($is_unhtml) {
            $name = unhtml($name);
        }
        echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($name, true) . "</pre>";
    }
}

if (!function_exists('C')) {
    /**
     * 配置信息调用
     * @param  string $class 配置文件
     * @param  string $key   配置项
     * @param  string $value 对于值
     * @return all
     */
    function C($class = NULL, $name = NULL, $value = NULL) {
        static $config = array();
        #判断如果传入的是数组，则叠加覆盖
        if (is_array($name)) {
            if (isset(reset($name)['name']) && isset(reset($name)['value'])) {
                $raw_array      = isset($config[$class]) ? $config[$class] : array();
                $config[$class] = @array_merge($raw_array, $name);
                return;
            } else {
                $raw_array = isset($config[$class]) ? $config[$class] : array();
                $new_array = array();
                foreach ($name as $key => $value) {
                    $new_array[$key]['name']  = $key;
                    $new_array[$key]['value'] = $value;
                }
                $config[$class] = @array_merge($raw_array, $new_array);
                return;
            }
        }
        #判断：如果传入的新配置项是字符串，则将字符串转换为数组
        if (is_string($name)) {
            #判断：如果传入的字符串没有点
            if (!strstr($name, ".")) {
                #判断：传入的配置项的值为空，则查找该配置是否存在，如果存在将该配置项设置为空
                if (is_null($value)) {
                    return isset($config[$class][$name]['value']) ? $config[$class][$name]['value'] : null;
                } else {
                    $config[$class][$name]['value'] = $value;
                    return;
                }
            }
        }
        #判断配置器是否为空
        if (!empty($config)) {
            #判断：如果没有传入新的配置项，则返回该配置项
            if (is_null(@$name)) {
                $data = $config[$class];
                $new  = array();
                foreach ($data as $key => $value) {
                    $new[$key] = $value['value'];
                }
                return $new;
            }
        }
    }
}

if (!function_exists('F')) {
    /**
     * 文件缓存
     *
     * @param $name
     * @param string $value
     * @param string $path
     *
     * @return bool
     */
    function F($name, $value = '[get]', $path = 'temp/file') {
        static $cache = [];

        $file = $path . '/' . $name . '.php';

        if ($value == '[del]') {
            if (is_file($file)) {
                unlink($file);
                if (isset($cache[$name])) {
                    unset($cache[$name]);
                }
            }
            return TRUE;
        }

        if ($value === '[get]') {
            if (isset($cache[$name])) {
                return $cache[$name];
            } else if (is_file($file)) {
                return $cache[$name] = include $file;
            } else {
                return FALSE;
            }
        }
        $data = "<?php if(!defined('ROOT_DIR'))exit;\nreturn " . var_export($value, TRUE) . ";\n?>";

        if (!is_dir($path)) {
            mkdir($path, 0755, TRUE);
        }

        if (!file_put_contents($file, $data)) {
            return FALSE;
        }

        $cache[$name] = $value;

        return TRUE;
    }
}

if (!function_exists('ret')) {
    /**
     * 页面数据返回
     * @param  integer $code 错误码
     * @param  string  $msg  消息提示 如果是数组 0 表示成功  1表示失败
     * @param  array   $body 需要返回的数据
     * @param  integer $type 返回数据类型 0直接返回不清缓存区 1清除数据再返回 2强制清空缓存返回数据
     * @return string  返回数据格式
     */
    function ret($code = 0, $msg = '', $body = [], $type = 1) {
        $browser = get_browsers();
        $array   = array();
        #判断是否数据
        if (is_array($msg)) {
            if (empty($body)) {
                $array = array(
                    'code' => -2,
                    'msg'  => $msg[1],
                    'data' => [],
                );
            } else {
                $msg = $msg[0];
            }
        }

        #判断是否Code失效
        if (!isset($array['code'])) {
            $array = array(
                'code' => $code,
                'msg'  => $msg,
                'data' => $body,
            );
        }

        #强制清除数据
        if ($type === 2) {
            ob_end_clean();
        }
        #判断浏览器
        if (!$browser['state'] || IS_POST || IS_AJAX || util::getHttpHeader('x-this7-client')) {
            if ($type == 1 || $type == 2) {
                ob_end_clean();
                $array = to_json($array);
                if (THIS7_DEFEND) {
                    $array = encrypt($array, THIS7_KEYS);
                }
                echo $array;
                exit();

            }
        }
        return $array;
    }
}

if (!function_exists('export')) {
    /**
     * 打印输出数据
     * @param  string $name  名称/数据
     * @param  string $value 对应值
     */
    function export($name, $value = '') {
        if (empty($value)) {
            echo "<pre>" . print_r($name, true) . "</pre>";
        } else {
            echo "<pre>" . $name . ":" . $value . "</pre>";
        }
    }
}

if (!function_exists('is_json')) {
    /**
     * 判断是否是JSON
     * @param  json  $string   需要判断的数据
     * @return boolean
     */
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}

if (!function_exists('to_json')) {
    /**
     * 数组转JSON
     * @param  array  $array 数组数据
     * @return json          返回JSON数据
     */
    function to_json($array = array()) {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

}

if (!function_exists('to_array')) {
    /**
     * JSON转数组
     * @param  string $json JSON数据
     * @return array        返回数组数据
     */
    function to_array($json = '') {
        return json_decode($json, true);
    }
}

if (!function_exists('get_json')) {
    /**
     * 获取JSON并自动转数组
     * @param  string  $file JSON文件
     * @param  boolean $is_array   是否以输出输出,默认TRUE
     * @param  boolean $rm_comment 是否去掉注释,默认TRUE
     * @return json     返回JSON数据
     */
    function get_json($file, $is_array = true, $rm_comment = true) {
        $json_string = file_get_contents($file);
        if ($rm_comment) {
            $json_string = remove_comment($json_string);
        }
        if ($is_array) {
            return to_array($json_string);
        } else {
            return $json_string;
        }
    }
}

if (!function_exists('remove_comment')) {
    /**
     * 去除PHP代码注释
     * @param  string $content 代码内容
     * @return string 去除注释之后的内容
     */
    function remove_comment($content) {
        $content = preg_replace("/\:\/\//s", '@ubhtpp@', $content);
        $content = preg_replace("/(\/\*.*\*\/)|(#.*?\n)|(\/\/.*?\n)/s", '', str_replace(array("\r\n", "\r"), "\n", $content));
        $content = preg_replace("/@ubhtpp@/s", '://', $content);
        return $content;
    }

}

if (!function_exists('get_size')) {
    /**
     * 根据大小返回标准单位 KB  MB GB等.
     * @param    int     $size
     * @param    int     $decimals 小数位
     * @return    string
     */
    function get_size($size, $decimals = 2) {
        switch (true) {
        case $size >= pow(1024, 3):
            return round($size / pow(1024, 3), $decimals) . ' GB';
        case $size >= pow(1024, 2):
            return round($size / pow(1024, 2), $decimals) . ' MB';
        case $size >= pow(1024, 1):
            return round($size / pow(1024, 1), $decimals) . ' KB';
        default:
            return $size . 'B';
        }
    }

}

if (!function_exists('to_mkdir')) {
    /**
     * 创建目录
     * @param    string    $path     目录名称，如果是文件并且不存在的情况下会自动创建
     * @param    string    $data     写入数据
     * @param    bool    $is_full  完整路径，默认False
     * @param    bool    $is_cover 强制覆盖，默认False
     * @return   bool    True|False
     */
    function to_mkdir($path = null, $data = null, $is_full = false, $is_cover = false) {
        $file = $path;
        #非完整路径进行组合
        if (!$is_full) {
            $path = ROOT_DIR . '/' . ltrim(ltrim($path, './'), '/');
        }
        #检测是否为文件
        $file_suffix = pathinfo($path, PATHINFO_EXTENSION);
        if ($file_suffix) {
            $path = pathinfo($path, PATHINFO_DIRNAME);
        } else {
            $path = rtrim($path, '/');
        }
        #执行目录创建
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
            chmod($path, 0777);
        }
        #文件则进行文件创建
        if ($file_suffix) {
            if (!file_exists($file)) {
                if (!file_put_contents($file, $data)) {
                    return false;
                }
            } else {
                #强制覆盖
                if ($is_cover) {
                    if (!file_put_contents($file, $data)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}

if (!function_exists('on_del')) {
    /**
     * 删除目录
     * @param  string $dir 需要删除的目录
     * @return   bool    True|False
     */
    function on_del($dir) {
        if (!is_dir($dir)) {
            return TRUE;
        }
        foreach (glob($dir . "/*") as $v) {
            is_dir($v) ? $this->del($v) : unlink($v);
        }

        return rmdir($dir);
    }
}

if (!function_exists('encrypt')) {
    /**
     * 信息加密函数
     * @param  string $data 需要加密数据
     * @param  string $key  加解密秘钥
     * @return string       返回加密数据
     */
    function encrypt($data = "", $key = "") {
        $char = $str = null;
        $key  = md5($key);
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }
}

if (!function_exists('decrypt')) {
    /**
     * 信息解密数据
     * @param  string $data 被加密字符串
     * @param  string $key  加解密秘钥
     * @return string       返回解密数据
     */
    function decrypt($data = "", $key = "") {
        $char = $str = null;
        $key  = md5($key);
        $x    = 0;
        $data = base64_decode($data);
        $len  = strlen($data);
        $l    = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }
}

if (!function_exists('array_remove')) {
    /**
     * 删除制定KEY的数组
     * @param  [type] $data 操作数据
     * @param  [type] $key  建值
     * @return [type]       [description]
     */
    function array_remove($data, $key) {
        if (!array_key_exists($key, $data)) {
            return $data;
        }
        $keys  = array_keys($data);
        $index = array_search($key, $keys);
        if ($index !== FALSE) {
            array_splice($data, $index, 1);
        }
        return $data;

    }
}

if (!function_exists('get_post')) {
    /**
     * 获取数据.
     *
     * @param string $data 定义变量
     *
     * @return [type] [description]
     */
    function get_post($data = '') {
        if ($_POST) {
            $data = $_POST;
        } else {
            $data = file_get_contents('php://input');
        }
        if (is_array($data)) {
            return $data;
        } elseif (is_json($data)) {
            return to_array($data);
        } else {
            return $data;
        }
    }
}

if (!function_exists('get_browsers')) {
    /**
     * 获取浏览器版本号
     * @return [type] [description]
     */
    function get_browsers() {
        global $_SERVER;
        $agent       = $_SERVER['HTTP_USER_AGENT'];
        $browser     = '';
        $browser_ver = '';

        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser     = 'OmniWeb';
            $browser_ver = $regs[2];
        }

        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser     = 'Netscape';
            $browser_ver = $regs[2];
        }

        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser     = 'Safari';
            $browser_ver = $regs[1];
        }

        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser     = 'Internet Explorer';
            $browser_ver = $regs[1];
        }

        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser     = 'Opera';
            $browser_ver = $regs[1];
        }

        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser     = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
            $browser_ver = $regs[1];
        }

        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser     = '(Internet Explorer ' . $browser_ver . ') Maxthon';
            $browser_ver = '';
        }
        if (preg_match('/360SE/i', $agent, $regs)) {
            $browser     = '(Internet Explorer ' . $browser_ver . ') 360SE';
            $browser_ver = '';
        }
        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser     = '(Internet Explorer ' . $browser_ver . ') 搜狗';
            $browser_ver = '';
        }

        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser     = 'FireFox';
            $browser_ver = $regs[1];
        }

        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser     = 'Lynx';
            $browser_ver = $regs[1];
        }

        if (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
            $browser     = 'Chrome';
            $browser_ver = $regs[1];

        }

        if ($browser != '') {
            if ($browser == '') {

            }
            return ['browser' => $browser, 'version' => $browser_ver, 'state' => true];
        } else {
            return ['browser' => 'unknow browser', 'version' => 'unknow browser version', 'state' => false];
        }
    }
}

if (!function_exists('get_sn')) {
    /**
     * 获取SN唯一编号
     * @return [type] [description]
     */
    function get_sn() {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $Sn    = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $Sn;
    }
}

if (!function_exists('get_ip')) {
    /**
     * 客户端IP地址获取
     * @param  integer $type [description]
     * @return [type]        [description]
     */
    function get_ip($type = 0) {
        $type      = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) {
            return $ip[$type];
        }
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            #nginx 代理模式下，获取客户端真实IP
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            #客户端的ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            #浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            #浏览当前页面的用户计算机的ip地址
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        # IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('unhtml')) {
    /**
     * 将HTML转普通字符串
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    function unhtml($content) {
        #定义自定义函数的名称
        $content = htmlspecialchars($content);
        #转换文本中的特殊字符
        $content = str_ireplace(chr(13), "<br>", $content);
        #替换文本中的换行符
        $content = str_ireplace(chr(32), " ", $content);
        #替换文本中的
        $content = str_ireplace("[_[", "<", $content);
        #替换文本中的小于号
        $content = str_ireplace(")_)", ">", $content);
        #替换文本中的大于号
        $content = str_ireplace("|_|", " ", $content);
        #替换文本中的空格
        return trim($content);
        #删除文本中首尾的空格
    }
}
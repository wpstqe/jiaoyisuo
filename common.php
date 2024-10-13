<?php
// 应用公共文件

use app\common\FoxCommon;
use app\common\service\AuthService;
use think\facade\Cache;


defined('DECIMAL_SCALE') || define('DECIMAL_SCALE', 8);
bcscale(DECIMAL_SCALE);

if (!function_exists('__url')) {

    /**
     * 构建URL地址
     * @param string $url
     * @param array $vars
     * @param bool $suffix
     * @param bool $domain
     * @return string
     */
    function __url(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        return url($url, $vars, $suffix, $domain)->build();
    }
}

if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param $value 需要加密的值
     * @param $type  加密类型，默认为md5 （md5, hash）
     * @return mixed
     */
    function password($value)
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }

}

if (!function_exists('xdebug')) {

    /**
     * debug调试
     * @param string|array $data 打印信息
     * @param string $type 类型
     * @param string $suffix 文件后缀名
     * @param bool $force
     * @param null $file
     */
    function xdebug($data, $type = 'xdebug', $suffix = null, $force = false, $file = null)
    {
        !is_dir(runtime_path() . 'xdebug/') && mkdir(runtime_path() . 'xdebug/');
        if (is_null($file)) {
            $file = is_null($suffix) ? runtime_path() . 'xdebug/' . date('Ymd') . '.txt' : runtime_path() . 'xdebug/' . date('Ymd') . "_{$suffix}" . '.txt';
        }
        file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . "========================= {$type} ===========================" . PHP_EOL, FILE_APPEND);
        if(is_string($data)){
            $str = var_export($data, true). PHP_EOL;
        }else if(is_array($data) || is_object($data)){
            $str = print_r($data, true). PHP_EOL;
        }
        $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }
}

if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed
     */
    function sysconfig($group, $name = null)
    {
        $where = ['group' => $group];
        $value = empty($name) ? Cache::get("sysconfig_{$group}") : Cache::get("sysconfig_{$group}_{$name}");
        if (empty($value)) {
            if (!empty($name)) {
                $where['name'] = $name;
                $value = \app\admin\model\SystemConfig::where($where)->value('value');
                Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}

if (!function_exists('array_format_key')) {

    /**
     * 二位数组重新组合数据
     * @param $array
     * @param $key
     * @return array
     */
    function array_format_key($array, $key)
    {
        $newArray = [];
        foreach ($array as $vo) {
            $newArray[$vo[$key]] = $vo;
        }
        return $newArray;
    }

}

if (!function_exists('auth')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth($node = null)
    {
        $authService = new AuthService(session('admin.id'));
        $check = $authService->checkNode($node);
        return $check;
    }

}

if (!function_exists('modnum')) {

    function modnum($num)
    {
        return intval($num/10);
    }

}

function p($arr)
{
    print_r($arr);
}

function pp($arr)
{
    var_dump($arr);
}

function get_lang()
{
    return \think\facade\Cookie::get(\think\facade\Config::get('lang.cookie_var'));
}

function round_pad_zero($num, $precision)
{
    if ($precision < 1) {
        return round($num, 0);
    }
    $r_num = round($num, $precision);
    $num_arr = explode('.', "$r_num");
    if (count($num_arr) == 1) {
        return "$r_num" . '.' . str_repeat('0', $precision);
    }
    $point_str = "$num_arr[1]";
    if (strlen($point_str) < $precision) {
        $point_str = str_pad($point_str, $precision, '0');
    }
    return $num_arr[0] . '.' . $point_str;
}

function arrayToObject($arr) {
    if(is_array($arr)) {
        return (object)array_map(__FUNCTION__, $arr);
    }else {
        return $arr;
    }
}
//二维数组转化为字符串，中间用,隔开
function arr_to_str($arr) {
    $t = '';
    foreach ($arr as $v) {
        $v = join(",",$v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
        $temp[] = $v;
    }
    foreach ($temp as $v) {
        $t.=$v." ";
    }
    $t = substr($t, 0, -1); // 利用字符串截取函数消除最后一个逗号
    return $t;
}
function phpqrcode($title,$name){
    require_once \think\facade\App::getRootPath().'extend/phpqrcode/phpqrcode.php';
    $data = $title;//内容
    $level = 'L';// 纠错级别：L、M、Q、H
    $size = 10;//元素尺寸
    $margin = 1;//边距
    $outfile = 'erweima.png';
    $saveandprint = false;// true直接输出屏幕  false 保存到文件中
    $back_color = 0xFFFFFF;//白色底色
    $fore_color = 0x000000;//黑色二维码色
    $QRcode = new \QRcode();
    $logoin = false; //生成logo
    ob_start();
    $QR = $name .'.png';
    $QRcode->png($data,$saveandprint,$level,$size,$margin,$back_color,$fore_color);
    //加上erweima.png会在根目录生成这个名字的二维码图片
    //带有logo的二维码beginning
    $logo = \think\facade\App::getRootPath().'/public/index/img/logo.png';
    if($logoin !== false){
        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        imagepng($QR,$QR);
        //带有logo的二维码ending
    }
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();
    $src = "data:image/jpg;base64,".$imageString;
    return $src;
}

/**
 * 服务器地址
 * 协议和域名
 *
 * @return string
 */
function server_url()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        $http = 'https://';
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        $http = 'https://';
    } else {
        $http = 'http://';
    }
    
    $host = $_SERVER['HTTP_HOST'];
    $res  = $http . $host;
   
    return $res;
}

/**
 * 文件地址
 * 协议，域名，文件路径
 *
 * @param string $file_path 文件路径
 * 
 * @return string
 */
function file_url($file_path = '')
{
    if (empty($file_path)) {
        return '';
    }

    if (strpos($file_path, 'http') !== false) {
        return $file_path;
    }

    $server_url = server_url();

    if (stripos($file_path, '/') === 0) {
        $res = $server_url . $file_path;
    } else {
        $res = $server_url . '/' . $file_path;
    }

    return $res;
}

/**
 * @Title: 真实IP
 * @Description: 
 */
function getRealIp()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

/**
 * @Title: 手机判断
 */
if (!function_exists('isMobile')) {
    function isMobile()
    {
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        if (isset ($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}

/**
 * @Title: 星号隐藏中间，前后取3
 * @param {*} $num
 */
function fox_star_name($num=''){
    if(!$num){
        return '';
    }
    $a = substr($num, 0 , 3);
    $b = substr($num, -3);
    return $a.'****'.$b;
}

/**
 * @Title: 截取字符串
 * @param {*} $text
 * @param {*} $length
 * @param {*} $type
 */
function subtext($text, $length, $type=0)
{
    if($type==0){
        $w = '...';
    }else{
        $w = '';
    }
    if(mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8').$w;
    } else {
        return $text;
    }
}
/**
 * @Title: 通用游戏算法
 * @param {*} $arr
 */
function get_rand($arr=[]) { 
    $result = ''; 
    //概率数组的总概率精度 
    $proSum = array_sum($arr); 
    //概率数组循环 
    foreach ($arr as $key => $proCur) { 
        $randNum = mt_rand(1, $proSum); 
        if ($randNum <= $proCur) { 
            $result = $key; 
            break; 
        } else { 
            $proSum -= $proCur; 
        } 
    } 
    unset ($arr);
    return $result; 
} 
/**
 * @Title: 唯一标识
 * @param {*} $prefix
 */
function uuid($prefix = '-FOX-') {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . '-';
    $uuid .= substr($chars, 8, 4) . '-';
    $uuid .= substr($chars, 12, 4) . '-';
    $uuid .= substr($chars, 16, 4) . '-';
    $uuid .= substr($chars, 20, 12);
    return $prefix . $uuid;
}

/**
 * @Title: 某的倍数
 * @param {*} $anum 查询的数
 * @param {*} $bnum 参数数
 */
function find_multiple($anum=100, $bnum=100){
    if(!$anum || !$bnum){
        return 0;
    }
    if (is_int($anum / $bnum)) {
        return ($anum / $bnum);
    } else {
        return 0;
    }
}
/**
 * @Title: 随机英文名
 * @param {*} $length
 * @param {*} $type
 * @param {*} $convert
 */
function random_code($length=6, $type='string', $convert=0){
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );
    
    if(!isset($config[$type])) $type = 'string';
    $string = $config[$type];
    
    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string[mt_rand(0, $strlen)];
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/**
 * @Title: 替换对应字符
 * @param {*} $str
 * @param {*} $nstr
 * @param {*} $t 为1时加重
 */
function fox_all_replace($str='', $nstr='', $t=0){
    $find = array("s%","S%");
    if($t==0){
        return str_replace($find, $nstr, $str);
    }else if($t==1){
        return str_replace($find, '<b>'.$nstr.'</b>', $str);
    }else if($t==2){
        return str_replace($find, '<span>'.$nstr.'</span>', $str);
    }else if($t==3){
        return str_replace($find, '<span class="color-green mr-10">'.$nstr.'</span>', $str);
    }else if($t==4){
        return str_replace($find, '<span class="color-red mr-10">'.$nstr.'</span>', $str);
    } if($t==5){
        return str_replace($find, '<span class="color-yellow mr-10">'.$nstr.'</span>', $str);
    }
}

function get_bottom($cate_id,$lang){
    $where = ['cate_id'=>$cate_id];
    $list = \app\admin\model\NewsLists::where($where)->where('status',1)->field('id')->select();
    if($list){
        $outhtml = '';
        foreach($list as $k => $v){
            $langinfo = \app\admin\model\LangLists::where('item','news')->where('item_id', $v['id'])->where('lang', $lang)->find();
            if($langinfo){
                $outhtml .= '<li><a href="'.url('show/news',['id'=>$v['id']]).'">'.$langinfo['title'].'</a></li>';
            }
        }
        return $outhtml;
    }
}
/**
 * @Title: 数字显示正号
 * @param {*} $num
 */
function fox_nums($num=0)
{
    if($num > 0){
        return '+'.$num;
    }else{
        return $num;
    }
}

/**
 * @Title: 计算当前时间分
 * @param {*} $the_time
 */
function fox_time($the_time=0) {  
    if((int)$the_time <= 0){
        $dur = time();
    }else{
        $dur = $the_time;
    }
    $htime = date("H:i",$dur);
    $mtime = date("i",$dur);
    $fourtime = ['00:00','04:00','08:00','12:00','16:00','20:00'];
    $daytime = ['00:00'];
    $m = ',1min';
    // 5分钟
    if($mtime % 5 == 0){
        $m = $m.',5min';
    } 
    if($mtime % 15 == 0){
        $m = $m.',15min';
    }  
    if($mtime % 30 == 0){
        $m = $m.',30min';
    }  
    if($mtime % 60 == 0){
        $m = $m.',60min';
    } 
    if(in_array($htime,$fourtime))
    {
        $m = $m.',4hour';
    }
    if(in_array($htime,$daytime))
    {
        $m = $m.',1day';
    }
    return $m;
} 
//浮点相等判断
function floatcmp($f1,$f2,$precision = 10)
{
  $e = pow(10,$precision);
  $i1 = intval($f1 * $e);
  $i2 = intval($f2 * $e);
  return ($i1 == $i2);
}
//两个任意精度数字的加法计算
function bc_add($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcadd', $left_operand, $right_operand, $out_scale);
}
//两个任意精度数字的减法
function bc_sub($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcsub', $left_operand, $right_operand, $out_scale);
}
//两个任意精度数字乘法计算
function bc_mul($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcmul', $left_operand, $right_operand, $out_scale);
}
//两个任意精度的数字除法计算
function bc_div($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcdiv', $left_operand, $right_operand, $out_scale);
}
//任意精度数字取模
function bc_mod($left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    return bc_method('bcmod', $left_operand, $right_operand, $out_scale);
}
//比较两个任意精度的数字
function bc_comp($left_operand, $right_operand)
{
    return bc_method('bccomp', $left_operand, $right_operand);
}
//任意精度数字的乘方
function bc_pow($left_operand, $right_operand)
{
    return bc_method('bcpow', $left_operand, $right_operand);
}
function bc_method($method_name, $left_operand, $right_operand, $out_scale = DECIMAL_SCALE)
{
    $left_operand = number_format($left_operand, DECIMAL_SCALE, '.', '');
    $method_name != 'bcpow' && $right_operand = number_format($right_operand, DECIMAL_SCALE, '.', '');
    $result = call_user_func($method_name, $left_operand, $right_operand);
    return $method_name != 'bccomp' ? number_format($result, $out_scale, '.', '') : $result;
}
function fox_raw($str){
    $str = str_replace(array("\r\n","\n","\r"), "</br>", $str);
    return $str;
}

function first_last_this_month(){
    $firstday = date('Y-m-01', strtotime(date("Y-m-d")));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return([$firstday,$lastday]);
}

function this_month_day(){
    $firstday = date('Y-m-01', strtotime(date("Y-m-d")));
    return date('t', strtotime($firstday));
}
/**
 * @Title: 输出26字母
 * @param {*} $num
 * @param {*} $type 1为大写
 */
function fox_abcdefg($num=0,$type=1){
    if(!$num){
        return false;
    }
    $n = (int)$num + 64;
    if($num >=64 || $num < 91){
        if($type == 0){
            return strtolower(chr($n));
        }else{
            return strtoupper(chr($n));
        }
    }else{
        return false;
    }
}

function fox_abc_slice($num=0)
{
    $input = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
    $output = array_slice($input, 0, $num);
    return $output;
}
function fox_team_on($key,$n=1){
    if($n==1){
        return (FoxCommon::find_seconds_rate($key)*100).'%';
    }else if($n==2){
        return (FoxCommon::find_upgood_rate($key)*100).'%';
    }else if($n==3){
        return (FoxCommon::find_types_rate('recharge',$key)*100).'%';
    }
}
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
function foxmat_seconds($second){
    if($second <= 60){
        return $second.lang('time_format.second');
    }else{
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);
        $hour = floor($second/3600);
        $second = $second%3600;
        $minute = floor($second/60);
        $second = $second%60;
        $to = '';
        if($day>0){
            $to = $to.$day.lang('time_format.day');
        }
        if($hour>0){
            $to = $to.$hour.lang('time_format.hour');
        }
        if($minute>0){
            $to = $to.$minute.lang('time_format.minute');
        }
        if($second>0){
            $to = $to.$second.lang('time_format.second');
        }
        return $to;
    }
	
}
/**
 * 生成宣传海报
 * @param array  参数,包括图片和文字
 * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
 * @return [type] [description]
 */
function createPoster($config=array(),$filename=""){
    //如果要看报什么错，可以先注释调这个header
    if(empty($filename)) header("content-type: image/png");
    $imageDefault = array(
      'left'=>0,
      'top'=>0,
      'right'=>0,
      'bottom'=>0,
      'width'=>100,
      'height'=>100,
      'opacity'=>100
    );
  
    $textDefault = array(
      'text'=>'',
      'left'=>0,
      'top'=>0,
      'fontSize'=>32,       //字号
      'fontColor'=>'255,255,255', //字体颜色
      'angle'=>0,
    );
  
    $background = $config['background'];//海报最底层得背景
    //背景方法
  
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);
    // imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
    //处理了图片
  
    if(!empty($config['image'])){
      foreach ($config['image'] as $key => $val) {
        $val = array_merge($imageDefault,$val);
        $info = getimagesize($val['url']);
        $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
        if($val['stream']){   //如果传的是字符串图像流
          $info = getimagesizefromstring($val['url']);
          $function = 'imagecreatefromstring';
        }
  
        $res = $function($val['url']);
        $resWidth = $info[0];
        $resHeight = $info[1];
        //建立画板 ，缩放图片至指定尺寸
        $canvas=imagecreatetruecolor($val['width'], $val['height']);
        imagefill($canvas, 0, 0, $color);
        //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
        imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
        $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
        $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
  
        //放置图像
        imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
      }
    }
    //处理文字
    if(!empty($config['text'])){
      foreach ($config['text'] as $key => $val) {
        $val = array_merge($textDefault,$val);
        list($R,$G,$B) = explode(',', $val['fontColor']);
        $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
        $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
        $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
        imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
      }
    }
  
    //生成图片
    if(!empty($filename)){
      $res = imagejpeg ($imageRes,$filename,90); //保存到本地
      imagedestroy($imageRes);
      if(!$res) return false;
      return $filename;
    }else{
      imagejpeg ($imageRes);     //在浏览器上显示
      imagedestroy($imageRes);
    }
  }
  
/**
 * 生成宣传海报
 * @param array  参数,包括图片和文字
 * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
 * @return [type] [description]
 */
function createShare($config=array(),$filename=""){
    //如果要看报什么错，可以先注释调这个header
    if(empty($filename)) header("content-type: image/png");
    $imageDefault = array(
      'left'=>0,
      'top'=>0,
      'right'=>0,
      'bottom'=>0,
      'width'=>100,
      'height'=>100,
      'opacity'=>100
    );
  
    $textDefault = array(
      'text'=>'',
      'left'=>0,
      'top'=>0,
      'fontSize'=>32,       //字号
      'fontColor'=>'255,255,255', //字体颜色
      'angle'=>0,
    );
  
    $background = $config['background'];//海报最底层得背景
    //背景方法
  
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);
    // imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
    //处理了图片
  
    if(!empty($config['image'])){
      foreach ($config['image'] as $key => $val) {
        $val = array_merge($imageDefault,$val);
        $info = getimagesize($val['url']);
        $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
        if($val['stream']){   //如果传的是字符串图像流
          $info = getimagesizefromstring($val['url']);
          $function = 'imagecreatefromstring';
        }
  
        $res = $function($val['url']);
        $resWidth = $info[0];
        $resHeight = $info[1];
        //建立画板 ，缩放图片至指定尺寸
        $canvas=imagecreatetruecolor($val['width'], $val['height']);
        imagefill($canvas, 0, 0, $color);
        //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
        imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
        $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
        $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
  
        //放置图像
        imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
      }
    }
    //处理文字
    if(!empty($config['text'])){
      foreach ($config['text'] as $key => $val) {
        $val = array_merge($textDefault,$val);
        list($R,$G,$B) = explode(',', $val['fontColor']);
        $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
        $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
        $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
        imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],mb_convert_encoding($val['text'],'html-entities','utf-8') );
      }
    }
  
    //生成图片
    if(!empty($filename)){
      $res = imagejpeg ($imageRes,$filename,90); //保存到本地
      imagedestroy($imageRes);
      if(!$res) return false;
      return $filename;
    }else{
      imagejpeg ($imageRes);     //在浏览器上显示
      imagedestroy($imageRes);
    }
  }
  
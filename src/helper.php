<?php
// +----------------------------------------------------------------------
// | 大鲸PHP框架 [ WhalePHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 上海才硕信息科技有限公司
// +----------------------------------------------------------------------
// | 官方网站: http://whalephp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

// WhalePHP常量定义
const WHALEPHP_VERSION    = '0.1';
const WHALEPHP_ADDON_PATH = './addons/';

if (!function_exists('helper_test')) {
    /**
	 * 测试
	 */
	function helper_test(){
		echo 'helper_test...';
	}
}

function TBui( $type,&$obj ){
	$builder = \whalephp\tbuilder\TBuilder::createBuilder( $type,$obj );
	
	
	
	return $builder;
}















//+---------------------------------------------------------------------------------+//
//+									插件及钩子相关处理								+//
//+---------------------------------------------------------------------------------+//
/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
	\Think\Hook::listen($hook,$params);
}

/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name){
	$dir_name 	= strtolower($name);
	// 	$class_name = ucfirst($dir_name);
	// 	$class = "\\addons\\{$dir_name}\\{$class_name}";

	$class_name = ucfirst($dir_name);
	$class = "\\addons\\{$dir_name}\\{$name}";

	return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name){
	$class = get_addon_class($name);
	if(class_exists($class)) {
		$addon = new $class();
		return $addon->getConfig();
	}else {
		return array();
	}
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()){
	$url        = parse_url($url);
	$case       = C('URL_CASE_INSENSITIVE');
	$addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
	$controller = $case ? parse_name($url['host']) : $url['host'];
	$action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

	/* 解析URL带的参数 */
	if(isset($url['query'])){
		parse_str($url['query'], $query);
		$param = array_merge($query, $param);
	}

	/* 基础参数 */
	$params = array(
			'_addons'     => $addons,
			'_controller' => $controller,
			'_action'     => $action,
	);
	$params = array_merge($params, $param); //添加额外参数

	return url('Addons/execute', $params);
}

if (!function_exists('addons_action_exists')) {
	/**
	 * 检查插件控制器是否存在某操作
	 * @param string $name 插件名
	 * @param string $controller 控制器
	 * @param string $action 动作
	 * @return bool
	 */
	function addons_action_exists($name = '', $controller = '', $action = '')
	{
		if (strpos($name, '/')) {
			list($name, $controller, $action) = explode('/', $name);
		}
		return method_exists("addons\\{$name}\\controller\\{$controller}", $action);
	}
}

if (!function_exists('addons_action')) {
	/**
	 * 执行插件动作
	 * 也可以用这种方式调用：plugin_action('插件名/控制器/动作', [参数1,参数2...])
	 * @param string $name 插件名
	 * @param string $controller 控制器
	 * @param string $action 动作
	 * @param mixed $params 参数
	 * @return mixed
	 */
	function addons_action($name = '', $controller = '', $action = '', $params = [])
	{
		if (strpos($name, '/')) {
			$params = is_array($controller) ? $controller : (array)$controller;
			list($name, $controller, $action) = explode('/', $name);
		}
		if (!is_array($params)) {
			$params = (array)$params;
		}
		$class = "addons\\{$name}\\controller\\{$controller}";
		$obj = new $class;

		return call_user_func_array([$obj, $action], $params);
	}
}
if (!function_exists('addons_model')) {
	/**
	 * 获取插件模型实例
	 * @param  string $name 插件名
	 * @return object
	 */
	function addons_model($name,$controller=null)
	{
		if( !$controller ){
			$controller = $name;
		}
		$class = "addons\\{$name}\\model\\{$controller}";
		return new $class;
	}
}
if (!function_exists('addons_model_exists')) {
	/**
	 * 检查插件模型是否存在
	 * @param string $name 插件名
	 * @return bool
	 */
	function addons_model_exists($name = '', $controller=null)
	{
		if( !$controller ){
			$controller = $name;
		}
		return class_exists("addons\\{$name}\\model\\{$name}");
	}
}




/**
 * 对象列表转数据数组列表
 * @date: 2017-4-23 下午1:16:01
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function getArrayList($objList){
	$list = [];
	foreach ($objList as $i=>$one){
		$list[] = $one->getData();
	}
	return $list;
}

/**
 * 实例化Model
 * @param string    $name Model名称
 * @param string    $layer 业务层名称
 * @param bool      $appendSuffix 是否添加类名后缀
 * @return \think\Model
 */
function M($name = '', $layer = 'model', $appendSuffix = false){
	return model($name, $layer, $appendSuffix );
}
/**
 * 获取获取当前请求的参数
 * @access public
 * @param string|array  $name 变量名
 * @param mixed         $default 默认值
 * @param string|array  $filter 过滤方法
 * @return mixed
 */
function I($name = '', $default = null){	//, $filter = ''
	$request = request();
	$param = $request->param();

	return ( isset($param[$name]) )?$param[$name]:$default;
}
/**
 * 获取和设置配置参数
 * @param string|array  $name 参数名
 * @param mixed         $value 参数值
 * @param string        $range 作用域
 * @return mixed
 */
function C($name = '', $value = null, $range = ''){
	return config($name, $value, $range);
}

/**
 * 创建数据构建器实例
 * @date: 2017-4-28 下午11:10:52
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function DBuilder( $modal_info ){
	return controller( 'common/DBuilder', 'builder')->setModel( $modal_info );
}
/**
 * 创建视图构建器实例
 * @date: 2017-4-29 下午12:50:43
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function TBuilder( $type,&$obj ){
	$builder = app\common\builder\TBuilder::createBuilder($type,$obj);
	if( $obj ){
		$builder->widgetsValues = &$obj->widgetsValues;
		//$builder->_view_vars 	= &$obj->_view_vars;
	}
	return $builder;
}

/**
 * 组件调用
 * @date: 2017-8-24 下午3:55:57
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function TWidget( $contObj=null )
{
	//$builder = app\common\builder\TWidget;
	$class = '\\app\\common\\builder\\TWidget';
	$builder = new $class($contObj);
	/*
	 if( $contObj ){
	$builder->_view_vars = &$contObj->_view_vars;
	}
	*/
	//$builder->_view_vars = [];
	return $builder;
}
function TWidget333( &$obj=null )
{
	//$builder = app\common\builder\TWidget;
	$class = '\\app\\common\\builder\\TWidget';
	$builder = new $class();
	if( $obj ){
		$builder->_view_vars = &$obj->_view_vars;
	}
	return $builder;
}


/**
 * 打印数组
 * @date: 2017-4-23 下午1:15:03
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function vd($arr){
	dump($arr);
}
/**
 * 打印数组并终止
 * @date: 2017-4-23 下午1:15:32
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function vde($arr){
	dump($arr);exit;
}
/*
 * microsecond 微秒     millisecond 毫秒
*返回时间戳的毫秒数部分
*/
function mtime()
{
	list($usec, $sec) = explode(" ", microtime());
	$msec = round($usec*1000);
	return $sec . $msec;

}
function json_vde($arr){
	echo json_encode($arr);exit;
}
function get_extension($file){
	return substr(strrchr($file, '.'), 1);
}
function sf($arr,$fpath='/alidata/www/html/yunceku/yunceku/runtime/log.php'){
	$data = "<?php\nreturn ".var_export($arr, true).";\n?>";
	file_put_contents($fpath,$data);
}
function parent_directory($path, $convert_backslashes = false) {
	// 检测是否包含反斜杠
	if( strstr($path, '\\') ) $backslash = true;
	// 将反斜杠转换成正斜杠
	$path = str_replace('\\', '/', $path);
	// 如果输入路径结尾包含斜杠，则自动加上
	if( substr($path, strlen($path) - 1) != '/' ) $path .= '/';
	// 获取父路径
	$path = substr($path, 0, strlen($path) - 1);
	$path = substr( $path, 0, strrpos($path, '/') ) . '/';
	// 转换回反斜杠
	if( !$convert_backslashes && $backslash ) $path = str_replace('/', '\\', $path);
	return $path;
}

//快速获取配置名称（便于模版中调用）
function cName($idValue,$configFieldName,$nameName='name'){
	$configContents = C( $configFieldName );
	return $configContents[$idValue][$nameName];
}

//基于数组创建目录和文件
function create_dir_or_files($files){
	foreach ($files as $key => $value) {
		if(substr($value, -1) == '/'){
			mkdir($value);
		}else{
			@file_put_contents($value, '');
		}
	}
}
/**
 * 递归创建目录
 * @param unknown $path
 * @return boolean
 */
function mkdirs($path) {
	if (! is_dir ( $path )) {
		mkdirs( dirname ( $path ) );
		if (! mkdir ( $path, 0777 )) {
			return false;
		}
	}
	return true;
}
/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array();
	if(is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId =  $data[$pid];
			if ($root == $parentId) {
				$tree[] =& $list[$key];
			}else{
				if (isset($refer[$parentId])) {
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
				}
			}
		}
	}
	return $tree;
}
// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
	$array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));

	if(strpos($string,':')){
		$value  =   array();
		foreach ($array as $val) {
			list($k, $v) = explode(':', $val);
			$value[$k]   = $v;
		}
	}else{
		$value  =   $array;
	}
	return $value;
}



/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
	$type       =  $type ? 1 : 0;
	static $ip  =   NULL;
	if ($ip !== NULL) return $ip[$type];
	if($adv){
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip     =   $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u",ip2long($ip));
	$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @param  null
 * @author  Jea杨
 * @return string
 */
function get_broswer(){
	$sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
	if (stripos($sys, "Firefox/") > 0) {
		preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
		$exp[0] = "Firefox";
		$exp[1] = $b[1];  //获取火狐浏览器的版本号
	} elseif (stripos($sys, "Maxthon") > 0) {
		preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
		$exp[0] = "傲游";
		$exp[1] = $aoyou[1];
	} elseif (stripos($sys, "MSIE") > 0) {
		preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
		$exp[0] = "IE";
		$exp[1] = $ie[1];  //获取IE的版本号
	} elseif (stripos($sys, "OPR") > 0) {
		preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
		$exp[0] = "Opera";
		$exp[1] = $opera[1];
	} elseif(stripos($sys, "Edge") > 0) {
		//win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
		preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
		$exp[0] = "Edge";
		$exp[1] = $Edge[1];
	} elseif (stripos($sys, "Chrome") > 0) {
		preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
		$exp[0] = "Chrome";
		$exp[1] = $google[1];  //获取google chrome的版本号
	} elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
		preg_match("/rv:([\d\.]+)/", $sys, $IE);
		$exp[0] = "IE";
		$exp[1] = $IE[1];
	}else {
		$exp[0] = "未知浏览器";
		$exp[1] = "";
	}
	return $exp[0].'('.$exp[1].')';
}

/**
 * 获取客户端操作系统信息包括win10
 * @param  null
 * @author  Jea杨
 * @return string
 */
function get_os(){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	$os = false;

	if (preg_match('/win/i', $agent) && strpos($agent, '95'))
	{
		$os = 'Windows 95';
	}
	else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
	{
		$os = 'Windows ME';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
	{
		$os = 'Windows 98';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
	{
		$os = 'Windows Vista';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
	{
		$os = 'Windows 7';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
	{
		$os = 'Windows 8';
	}else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
	{
		$os = 'Windows 10';#添加win10判断
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
	{
		$os = 'Windows XP';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
	{
		$os = 'Windows 2000';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
	{
		$os = 'Windows NT';
	}
	else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
	{
		$os = 'Windows 32';
	}
	else if (preg_match('/linux/i', $agent))
	{
		$os = 'Linux';
	}
	else if (preg_match('/unix/i', $agent))
	{
		$os = 'Unix';
	}
	else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
	{
		$os = 'SunOS';
	}
	else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
	{
		$os = 'IBM OS/2';
	}
	else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
	{
		$os = 'Macintosh';
	}
	else if (preg_match('/PowerPC/i', $agent))
	{
		$os = 'PowerPC';
	}
	else if (preg_match('/AIX/i', $agent))
	{
		$os = 'AIX';
	}
	else if (preg_match('/HPUX/i', $agent))
	{
		$os = 'HPUX';
	}
	else if (preg_match('/NetBSD/i', $agent))
	{
		$os = 'NetBSD';
	}
	else if (preg_match('/BSD/i', $agent))
	{
		$os = 'BSD';
	}
	else if (preg_match('/OSF1/i', $agent))
	{
		$os = 'OSF1';
	}
	else if (preg_match('/IRIX/i', $agent))
	{
		$os = 'IRIX';
	}
	else if (preg_match('/FreeBSD/i', $agent))
	{
		$os = 'FreeBSD';
	}
	else if (preg_match('/teleport/i', $agent))
	{
		$os = 'teleport';
	}
	else if (preg_match('/flashget/i', $agent))
	{
		$os = 'flashget';
	}
	else if (preg_match('/webzip/i', $agent))
	{
		$os = 'webzip';
	}
	else if (preg_match('/offline/i', $agent))
	{
		$os = 'offline';
	}
	else
	{
		$os = '未知操作系统';
	}
	return $os;
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function whale_md5($str, $key = 'WhalePHP'){
	return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string
 */
function think_ucenter_encrypt($data, $key, $expire = 0) {
	$key  = md5($key);
	$data = base64_encode($data);
	$x    = 0;
	$len  = strlen($data);
	$l    = strlen($key);
	$char =  '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x=0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	$str = sprintf('%010d', $expire ? $expire + time() : 0);
	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
	}
	return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function think_ucenter_decrypt($data, $key){
	$key    = md5($key);
	$x      = 0;
	$data   = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data   = substr($data, 10);
	if($expire > 0 && $expire < time()) {
		return '';
	}
	$len  = strlen($data);
	$l    = strlen($key);
	$char = $str = '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x = 0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		}else{
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}

//模版中使用函数区
//==============================================================
/**
 * 依据当前记录及配置获取url
 * @param
 * 		$config	例
 * 			url:menu/index
 * 			p:id.pid,title
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function get_turl($vo,$config){
	$parm 	= array();
	$p_arr 	= get_arr_val($config,'p',[]);//(isset($config['p']))?$config['p']:[];
	$p_arr	= explode(',', $p_arr);
	foreach ($p_arr as $p){
		$p 		= explode('.', $p);
		$p[1] 	= get_arr_val($p,1,$p[0]);
		$parm[ $p[1] ] = $vo[ $p[0] ];
	}
	return url($config['url'],$parm);
}
/**
 * 替换href中的变量
 * @param unknown $vo
 * @param unknown $config
 * @return Ambigous <unknown, mixed>
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function get_turl_replace($vo,$config){
	$href = $config['href'];
	foreach ($config['href_param_key'] as $key){
		if( isset( $vo[$key] ) ){
			$href = str_replace('__'.$key.'__', $vo[$key], $href);
		}
	}
	return $href;
}

/**
 * 获取表单组件的值
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function get_twval($widgetInfo){
	$val = $widgetInfo['value'];

	$val = get_func_val($val,$widgetInfo);

	return $val;
}

function get_func_val($val,$config){
	//通过指定的函数格式化数据
	if( isset($config['func']) && $config['func'] ){

		$func_param = (isset($config['func_param']))?$config['func_param']:null;

		if( $config['func']=='c_name' && empty($func_param) && isset($config['config_name']) ){
			$func_param = $config['config_name'];
		}
		if($func_param){
			$val 		= $config['func']($val,$func_param);
		}else{
			$val 		= $config['func']($val);
		}
	}

	return $val;
}


/**
 * 获取配置转换值
 */
function c_name($key,$conf_name){
	$conf = config($conf_name);
	// 	vd($key);vd($conf);
	if( $conf && isset($conf[$key]) ){
		if( is_array($conf[$key]) ){
			return $conf[$key]['name'];
		}else{
			return $conf[$key];
		}
	}
	return $key;
}









function get_parent_menu_name($pid){
	if(!$pid)return '无';

	// 	return $pid;
	return M('Menu')->where( array('id'=>$pid) )->value('title');
}


function success($msg){
	return ret_message($code=1, $msg);
}

function error($msg){
	return ret_message($code=0, $msg);
}

function ret_message($code=0, $msg = '', $url = null, $data = '', $wait = 3){
	if( $msg=='' ){
		$msg = ($code)?'操作成功':'操作失败';
	}
	$ret_data = [
	'code' => $code,
	'msg'  => $msg,
	'data' => $data,
	'url'  => $url,
	'wait' => $wait,
	];
	return $ret_data;
}




//==============================================================
/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login(){
	if( IS_CLI )return 0;
	$user = session('user_auth');
	if (empty($user)) {
		return 0;
	} else {
		return session('user_auth_sign') == data_auth_sign($user) ? $user['id'] : 0;
	}
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null){
	$uid = is_null($uid) ? is_login() : $uid;
	return $uid && ( in_array(intval($uid), C('user_administrator')) );
}


/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
	//数据类型检测
	if(!is_array($data)){
		$data = (array)$data;
	}
	ksort($data); //排序
	$code = http_build_query($data); //url编码并生成query字符串
	$sign = sha1($code); //生成签名
	return $sign;
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map  映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
	if($data === false || $data === null ){
		return $data;
	}
	$data = (array)$data;
	foreach ($data as $key => $row){
		foreach ($map as $col=>$pair){
			if(isset($row[$col]) && isset($pair[$row[$col]])){
				$data[$key][$col.'_text'] = $pair[$row[$col]];
			}
		}
	}
	return $data;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
	if(is_array($list)){
		$refer = $resultSet = array();
		foreach ($list as $i => $data)
			$refer[$i] = &$data[$field];
		switch ($sortby) {
			case 'asc': // 正向排序
				asort($refer);
				break;
			case 'desc':// 逆向排序
				arsort($refer);
				break;
			case 'nat': // 自然排序
				natcasesort($refer);
				break;
		}
		foreach ( $refer as $key=> $val)
			$resultSet[] = &$list[$key];
		return $resultSet;
	}
	return false;
}




/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
	$time = $time === NULL ? NOW_TIME : intval($time);
	return date($format, $time);
}

function datetime_format($time = NULL,$format='Y-m-d H:i:s'){
	return time_format($time,$format);
}

function get_username($uid = null){
	$uid = is_null($uid) ? is_login() : $uid;
	return model('app\common\model\Member')->where( ['id'=>$uid] )->value('username');

}
/**
 * 比较2个日期的差值
 * @param unknown $a
 * @param unknown $b
 */
function datetime_dvalue($a,$b){
	return strtotime($a) - strtotime($b);
}

// +----------------------------------------------------------------------
// | 行为日志相关
// +----------------------------------------------------------------------
/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @param int $organization_id 日志记录通知组织id
 * @return boolean
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null, $organization_id = 0){

	//参数检查
	if(empty($action) || empty($model) || empty($record_id)){
		return '参数不能为空';
	}
	if(empty($user_id)){
		$user_id = is_login();
	}

	//查询行为,判断是否执行
	$action_info = model('app\common\model\Action')->getByName($action)->toArray();
	if($action_info['status'] != 1){
		return '该行为被禁用或删除';
	}

	$NOW_TIME = time();

	//插入行为日志
	$data['action_id']      =   $action_info['id'];
	$data['user_id']        =   $user_id;
	$data['action_ip']      =   ip2long(get_client_ip(0,true));
	$data['model']          =   $model;
	$data['record_id']      =   $record_id;
	$data['create_time']    =   $NOW_TIME;
	$data['organization_id']=   $organization_id;

	//解析日志规则,生成日志备注
	if(!empty($action_info['log'])){
		if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
			$log['user']    =   $user_id;
			$log['record']  =   $record_id;
			$log['model']   =   $model;
			$log['time']    =   $NOW_TIME;
			$log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>$NOW_TIME);
			foreach ($match[1] as $value){
				$param = explode('|', $value);
				if(isset($param[1])){
					$replace[] = call_user_func($param[1],$log[$param[0]]);
				}else{
					$replace[] = $log[$param[0]];
				}
			}
			$data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
		}else{
			$data['remark'] =   $action_info['log'];
		}
	}else{
		//未定义日志规则，记录操作url
		$data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
	}

	model('app\common\model\ActionLog')->save($data);
	//think\Db::table('wp_action_log')->insert($data);

	if(!empty($action_info['rule'])){
		//解析行为
		$rules = parse_action($action, $user_id);

		//执行行为
		$res = execute_action($rules, $action_info['id'], $user_id);
	}
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self){
	if(empty($action)){
		return false;
	}

	//参数支持id或者name
	if(is_numeric($action)){
		$map = array('id'=>$action);
	}else{
		$map = array('name'=>$action);
	}

	//查询行为信息
	$info = M('Action')->where($map)->find()->toArray();
	if(!$info || $info['status'] != 1){
		return false;
	}

	//解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = trim($info['rule'],';');
	$rules = str_replace('{$self}', $self, $rules);
	$rules = explode(';', $rules);

	$return = array();
	foreach ($rules as $key=>&$rule){
		$rule = explode('|', $rule);
		foreach ($rule as $k=>$fields){
			$field = empty($fields) ? array() : explode(':', $fields);
			if(!empty($field)){
				$return[$key][$field[0]] = $field[1];
			}
		}
		//cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
			unset($return[$key]['cycle'],$return[$key]['max']);
		}
	}

	return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null){
	if(!$rules || empty($action_id) || empty($user_id)){
		return false;
	}

	$return = true;
	foreach ($rules as $rule){

		//检查执行周期
		$map = array('action_id'=>$action_id, 'user_id'=>$user_id);
		$map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
		$exec_count = M('ActionLog')->where($map)->count();
		if($exec_count > $rule['max']){
			continue;
		}

		//执行数据库操作
		$Model = M(ucfirst($rule['table']));
		$field = $rule['field'];
		$res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

		if(!$res){
			$return = false;
		}
	}
	return $return;
}



function is_check_html($current_id,$all_vals){
	if( strpos($all_vals, '[') === 0 ){
		$all_vals = json_decode($all_vals,true);
	}else if( !is_array($all_vals) ){
		$all_vals = explode(',', $all_vals);
	}
	if( in_array($current_id, $all_vals) ){
		return 'checked ';
	}
	return '';
}















/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 
function parse_name($name, $type=0) {
	if ($type) {
		return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
	} else {
		return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
	}
}
*/

//+=======================================================================
//+								数据处理
//+=======================================================================
function getArrEmptyValue($arr,$key,$default=0){
	return (isset($arr[$key]) && $arr[$key])?$arr[$key]:$default;
}
//从指定数组中提取某个字段组成新的索引数组
function getIdArr( $arr ,$idName='id'){
	$IdArr 	= array();
	foreach ($arr as $key=>$val){
		$IdArr[] = $val[$idName];
	}
	return $IdArr;
}

//从指定数组中提取某个字段为索引组成关联数组
function getIdIndexArr( $arr ,$idName='id'){

	if(empty($arr))return [];

	//$count 	= count($arr);
	$IdArr 	= array();
	//for($i=0;$i<$count;$i++){
	foreach ($arr as $key=>$val){
		if( !isset($val[$idName]) ){
			return [];
		}
		$IdArr[ $val[$idName] ] = $val;
	}
	return $IdArr;
}
function getIdIndexSubArr( $arr ,$idName='id',$use_key=FALSE){

	if(empty($arr))return [];
	$IdArr 	= array();
	foreach ($arr as $key=>$val){
		if( $use_key ){
			$IdArr[ $val[$idName] ][$key] = $val;
		}else{
			$IdArr[ $val[$idName] ][] = $val;
		}
	}
	return $IdArr;
}

function arr2multisort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_STRING ){
	if(is_array($arrays)){
		foreach ($arrays as $array){
			if(is_array($array)){
				$key_arrays[] = $array[$sort_key];
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
	array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
	return $arrays;
}

//+=======================================================================
//+								数据转换
//+=======================================================================
function arr2str($arr){
	if( empty($arr) )return '';
	$str = implode(',', $arr);

	return $str;
	//return ','.$str.',';
}
function str2arr($str){
	//$str = trim($str,',');
	return explode(',', $str);
}



//+=======================================================================
//+								数据获取
//+=======================================================================
function getTreeMenus($bdname,$is_get_true_tree=false,$append_data=[],$map=[]){
	//获取层级关系化的菜单
	$menus = db($bdname)->where($map)->field(true)->select();
	//$menus = getArrayList($menus);
	//vde($menus);
	if( $is_get_true_tree ){
		$menus = M('Tree','tool')->toTree($menus);
		//$menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
	}else{
		$menus = M('Tree','tool')->toFormatTree($menus);
		if( !empty($append_data) ){
			$menus = array_merge(array(0=>$append_data), $menus);
		}
	}
	return $menus;
}

/**
 * 生成一个随机码
 * @param number $length
 * @return string
 */
function generate_tatted_code( $length = 8,&$exist_code=[] ) {
	// 密码字符集，可任意添加你需要的字符
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';	//!@#$%^&*()-_ []{}<>~`+=,.;:/?|
	$code  = '';
	for ( $i = 0; $i < $length; $i++ ){
		$code .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	// 如果设置了已存在随机码，且当前生成的随机码在此数组中，则重新生成获取
	if( $exist_code && in_array($code, $exist_code) ){
		return generate_tatted_code($length,$exist_code );
	}
	return $code;
}
//+=======================================================================
//+								数据验证
//+=======================================================================

// 不区分大小写的in_array实现
function in_array_case($value,$array){
	return in_array(strtolower($value),array_map('strtolower',$array));
}




/**
 * 检查目录是否可写
 * @param unknown $path
 * @return boolean
 */
function check_path($path)
{
	if (is_dir($path)) {
		return true;
	}
	$path = str_replace('//', '/', $path);
	if (mkdir($path, 0755, true)) {
		return true;
	} else {
		//$this->error = "目录 {$path} 创建失败！";
		return false;
	}
}


/**
 * 是否是手机号码
 *
 * @param string $phone 手机号码
 * @return boolean
 */
function is_phone($phone) {
	if (strlen ( $phone ) != 11 || ! preg_match ( '/^1[3|4|5|7|8][0-9]\d{4,8}$/', $phone )) {
		return false;
	} else {
		return true;
	}
}
/**
 * 是否为一个合法的email
 * @param sting $email
 * @return boolean
 */
function is_email($email){
	if (filter_var ($email, FILTER_VALIDATE_EMAIL )) {
		return true;
	} else {
		return false;
	}
}
/**
 * 是否为一个合法的url
 * @param string $url
 * @return boolean
 */
function is_url($url){
	if (filter_var ($url, FILTER_VALIDATE_URL )) {
		return true;
	} else {
		return false;
	}
}
/**
 * 是否为合法的身份证(支持15位和18位)
 * @param string $card
 * @return boolean
 */
function is_card($card){
	if(preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/',$card)||preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/',$card))
		return true;
	else
		return false;
}
/**
 * 验证日期格式是否正确
 * @param string $date
 * @param string $format
 * @return boolean
 */
function is_date($date,$format='Y-m-d'){
	$t=date_parse_from_format($format,$date);
	if(empty($t['errors'])){
		return true;
	}else{
		return false;
	}
}





//+=======================================================================
//+								数据展示
//+=======================================================================
/**
 * 友好的时间显示
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendly_date($sTime,$type = 'normal',$alt = 'false') {
	if (!$sTime)
		return '';
	//sTime=源时间，cTime=当前时间，dTime=时间差
	$cTime      =   time();
	$dTime      =   $cTime - $sTime;
	$dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
	//$dDay     =   intval($dTime/3600/24);
	$dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
	//normal：n秒前，n分钟前，n小时前，日期
	if($type=='normal'){
		if( $dTime < 60 ){
			if($dTime < 10){
				return '刚刚';    //by yangjs
			}else{
				return intval(floor($dTime / 10) * 10)."秒前";
			}
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
			//今天的数据.年份相同.日期相同.
		}elseif( $dYear==0 && $dDay == 0  ){
			//return intval($dTime/3600)."小时前";
			return '今天'.date('H:i',$sTime);
		}elseif($dYear==0){
			return date("m月d日 H:i",$sTime);
		}else{
			return date("Y-m-d H:i",$sTime);
		}
	}elseif($type=='mohu'){
		if( $dTime < 60 ){
			return $dTime."秒前";
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
		}elseif( $dTime >= 3600 && $dDay == 0  ){
			return intval($dTime/3600)."小时前";
		}elseif( $dDay > 0 && $dDay<=7 ){
			return intval($dDay)."天前";
		}elseif( $dDay > 7 &&  $dDay <= 30 ){
			return intval($dDay/7) . '周前';
		}elseif( $dDay > 30 ){
			return intval($dDay/30) . '个月前';
		}
		//full: Y-m-d , H:i:s
	}elseif($type=='full'){
		return date("Y-m-d , H:i:s",$sTime);
	}elseif($type=='ymd'){
		return date("Y-m-d",$sTime);
	}else{
		if( $dTime < 60 ){
			return $dTime."秒前";
		}elseif( $dTime < 3600 ){
			return intval($dTime/60)."分钟前";
		}elseif( $dTime >= 3600 && $dDay == 0  ){
			return intval($dTime/3600)."小时前";
		}elseif($dYear==0){
			return date("Y-m-d H:i:s",$sTime);
		}else{
			return date("Y-m-d H:i:s",$sTime);
		}
	}
}
function format_time($t){
	$f=array(
			'31536000'=>'年',
			'2592000'=>'个月',
			'604800'=>'星期',
			'86400'=>'天',
			'3600'=>'小时',
			'60'=>'分钟',
			'1'=>'秒'
	);
	$ret = '';
	foreach ($f as $k=>$v){
		$k  = (int)$k;
		$c 	= floor($t/$k);
		$yu = $t%$k;

		if($c!=0)$ret .= $c .''. $v .'';
		if($yu==0)break;
		$t = $yu;
	}
	return $ret;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function friendly_byte($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

function proportionNum($num,$append='%'){
	return round($num*100) . $append;
}


function endwith($haystack,$needle){
	if(!$needle) return false;
	$nlen = strlen($needle);
	$strend = substr($haystack,-$nlen);
	return ($strend==$needle)?true:false;
}

/**
 * 获取树状展示
 * @param unknown $list
 * @param number $level
 * @return string
 */
function create_tree_html($list,$config=[],$level=1){
	$columns = $config['columns'];
	//vde($config);
	//vde($columns[2]);

	$html = '<ol class="dd-list" level="'.$level.'">';
	foreach ($list as $i=>$item){
		$id = $item['id'];
		$html .= 	'<li class="dd-item dd3-item" data-id="'.$id.'">'.
				'<div class="dd-handle dd3-handle fa fa-list-ul">Drag</div>'.
				'<div class="dd3-content">';

		$columns_box = '';
		foreach ($columns as $column){

			$column_config 	= $column['config'];
			//vde($column_config);

			// 元素类型
			$type 			= $column_config['type'];

			// 组合
			switch ( $type ){
				case 'field':	//普通字段
					// 单个元素前置图标
					$icon_i = '';
					if( isset($column_config['icon_prefix_class_field']) && !empty($column_config['icon_prefix_class_field']) ){
						$icon_i .= '<i class="'.$item[ $column_config['icon_prefix_class_field'] ].'"></i>&nbsp;';
					}
					if( isset($column_config['icon_prefix_class']) && !empty($column_config['icon_prefix_class']) ){
						$icon_i .= '<i class="'.$column_config['icon_prefix_class'].'"></i>&nbsp;';
					}

					//
					//$columns_box .= '<div>' . $icon_i . $item[ $column['field'] ].'</div>';
					$columns_box .= '<div>' . $icon_i . get_tval($item,$column) .'</div>';
					break;

				case 'btn':		//按钮
					/**/
					$btn_box  = '<div class="media-right">';

					$list_item_btns = $config['list_item_btns'];

					//vde($column);
					foreach ($list_item_btns as $btn){
						//$url = $btn['href'];
						//$btn_box .= '<a href="'.$url.'"><span class="fa fa-pencil text-muted mb5"></span></a>';
						$titleShow = (isset($btn['is_show_title']) && $btn['is_show_title']==1)?'<span class="fs10"> '.$btn['title'].'</span>':'';
						if( isset($btn['show_title']) && !empty($btn['show_title']) ){
							$titleShow = $btn['show_title'];
						}

						$btn_box .= '<a href="'.get_turl_replace($item,$btn).'" data-toggle="tooltip" data-placement="top" title="'.$btn['title'].'" '.$btn['attr_str'].' class="'.$btn['item_class'].' text-muted mb5 '. $btn['icon_class'].'">'.
								$titleShow.
								'</a>';
					}

					$btn_box .= '</div>';
						
					replaceVoFields($item,$btn_box);
						
					$columns_box .= $btn_box;

					break;
			}

		}

		//展示元素区
		$html .= $columns_box;

		/*
		 //展示元素区
		$html .= 			'<div><i class="'.$item['icon_class'].'"></i>'.$item['title'].'</div>'.
		'<span>'.
		'<i class="fa fa-link"></i> '.$item['url'].
		'</span>';
			
		//操作按钮区
		$html .= 			'<div class="media-right">'.
		'<a href="'.url('menu/edit',['id'=>$id]).'"><span class="fa fa-pencil text-muted mb5"></span></a>'.
		'<a href="'.url('menu/index',['pid'=>$id]).'"><span class="fa fa-list text-muted mb5"></span></a>'.
		//'<a href="'.url('menu/treelist',['pid'=>$id]).'"><span class="fa fa-sitemap text-muted mb5"></span></a>'.
		'<a href="'.url('menu/add',['pid'=>$id]).'"><span class="fa fa-plus text-muted mb5"></span></a>'.
		'<a href="'.url('menu/del',['id'=>$id]).'" class="ajax-get confirm"><span class="fa fa-remove text-danger-light"></span></a>'.
		'</div>';
		*/



		$html .=		'</div>';

		if( isset($item['_child']) ){
			$to_level = $level + 1;
			$html .= create_tree_html($item['_child'],$config,$to_level);
		}
		$html .=	'</li>';
	}
	$html .= '</ol>';

	return $html;
}

/**
 * 获取组件下图片组合展示
 * @param unknown $img_ids
 * @return string
 */
function get_pic_list_html($img_ids){
	$img_ids = explode(',', $img_ids);
	$html = '';
	foreach ($img_ids as $id){
		if( $id ){
			$html .= '<div class="pic_item"><img class="upload_img" data-id="'.$id.'" alt="" src="'.get_cover($id).'"><div class="del_pic">删除</div></div>';
		}
	}
	return $html;
}

/**
 * 获取组件下图片组合展示
 * @param unknown $img_ids
 * @return string
 */
function get_file_list_html($file_ids){
	$file_ids 	= explode(',', $file_ids);
	$file_list 	= model('File')->getList(['id'=>['in',$file_ids]]);

	$html = '';
	foreach ($file_list as $vo){
		if( $vo ){
			$html .= 	'<div class="file-item-upload" data-id="'.$vo['id'].'">'.
					'<i class="fa fa-file-o"></i> '.$vo['name'].
					'<span>'.$vo['friendly_size'].'</span>'.
					'<a class="del-file-item" href="javascript:;"><i class="fa fa-minus-circle"></i></a>'.
					'</div>';
		}
	}
	return $html;
}

/**
 * 依据图片ID获取图片地址
 * @param unknown $pic_id
 * @return Ambigous <\think\db\mixed, mixed, boolean, number, unknown, \think\mixed, PDOStatement, string>
 */
function get_cover($pic_id,$is_full=false){
	$path = db('admin_picture')->where( ['id'=>$pic_id] )->value('path');
	if( $is_full ){
		$path = \think\Request::instance()->root(true).$path;
	}

	$path = str_replace('\\', '/', $path);

	return $path;
}

function get_cover_img($pic_id){
	$path = get_cover($pic_id);
	return '<img src="'.$path.'">';
}


/**
 *
 * @date: 2017-4-29 上午12:47:46
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function yes_no($val){
	return $val?'是':'否';
}

/**
 * 检测数组中某个键是否存在，不存在则返回默认值
 * @date: 2017-4-28 下午11:21:55
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function get_arr_val($arr,$key,$default_val='',$filter_empty=false){

	$key = str_replace('[]', '', $key);

	$ret = (isset($arr[$key]))?$arr[$key]:$default_val;
	// 	vd($ret);vd($filter_empty);

	if( $filter_empty && empty($ret)){
		$ret = $default_val;
	}
	return $ret;
}

/**
 *
 * @date: 2017-9-13 上午9:44:27
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function getValueChecked($aim_value,$value,$ret='checked')
{
	if( is_array($value) && in_array($aim_value, $value) ){
		return $ret;
	}else if($aim_value==$value){
		return $ret;
	}
	return '';
}
/**
 *
 * @date: 2017-9-13 上午9:50:39
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function getValueSelected($aim_value,$value)
{
	return getValueChecked($aim_value,$value,'selected');
}
//+=======================================================================
//+								Builder辅助
//+=======================================================================
/**
 * 格式化栏目数组
 * 2017-12-24 下午7:54:51
 * qw_xingzhe <qwei2013@gmail.com>

 function formatColumns($columns)
 {
 $temp = [];
 foreach ($columns as $i=>$one){
 	
 //更多配置
 $one[2] = (isset($one[2]))?$one[2]:[];
 $defaultConfig = [
 //展示类型
 'type'				=> 'field',		//展示类型
 //field
 // field . xedit type
 //url
 //btn
 // type . url | 参数
 //type为field，对显示内容进行二次处理的函数
 'func'				=> '',			// 对字段处理的函数名称，参数为当前值
 'func_param'		=> '',			// 当存在对字段处理的函数时，此值为为该函数的第二个参数

 'table_field'		=> '',			// 获取指定表的指定字段的值，使用 | 分割表与表字段(影响速度，不建议使用)

 'config_name'		=> null,		// 使用配置中的数据值

 //附加数据select2
 'listdata'			=> [],			// 下拉的数据
 'key_relevance'		=> 'id|name',	// 使用的字段
 //'data_empty'		=> '',			// 此值一旦设置，则在未成功匹配到数据的时候，用此值替换

 //type为btn有效
 'btn_config'		=> [],			// 按钮相关配置

 //type为url有效
 'url'				=> '',			// 基础url,当type为url时生效
 'url_param'			=> '',			// 基础url参数配置,当type为url时生效

 //样式相关
 'style'				=> '',
 	
 //字段排序
 'order'				=> false,		// 是否开启，默认关闭
 'order_url'			=> '',
 'order_icon'		=> 'fa fa-sort text-muted',

 // 字段前后附加内容
 'prepend_content'	=> '',			// 向前追加
 'append_content'	=> '',			// 向后追加
 'bottom_html'		=> '',			// 换行追加
 ];
 	
 // 列表数据最终由get_tval格式化
 $config  = array_replace($defaultConfig,$one[2]);


 // 开启了字段排序
 if( $config['order'] ){
 $config['order_url'] = request()->url();
 }

 $tempOne = [
 'field'		=> $one[0],
 'name'		=> $one[1],
 'config'	=> $config,
 ];

 $temp[] = $tempOne;
 }

 return $temp;
 }
 */


/**
 * 匹配是否显示
 * 2017-12-21 上午11:45:28
 * qw_xingzhe <qwei2013@gmail.com>
 */
function getMapShowRet($vo,$show_map)
{
	$is_show_btn = true;
	foreach ($show_map as $field=>$one){	//one	0:条件，1：对比值
		switch ( $one[0] ){
			case 'eq':
				if( $vo[$field]!=$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'neq':
				if( $vo[$field]==$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'gt':
				if( $vo[$field]<=$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'egt':
				if( $vo[$field]<$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'lt':
				if( $vo[$field]>=$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'elt':
				if( $vo[$field]>$one[1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'in':
				if( is_string($one[1]) )$one[1] = explode(',', $one[1]);
				if( !in_array($vo[$field], $one[1]) ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'notin':
				if( is_string($one[1]) )$one[1] = explode(',', $one[1]);
				if( in_array($vo[$field], $one[1]) ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'between':
				if( is_string($one[1]) )$one[1] = explode(',', $one[1]);
				if( $vo[$field]<$one[1][0] || $vo[$field]>$one[1][1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
			case 'notbetween':
				if( is_string($one[1]) )$one[1] = explode(',', $one[1]);
				if( $vo[$field]>=$one[1][0] && $vo[$field]<=$one[1][1] ){
					$is_show_btn = false;
					break 2;
				}
				break;
		}
	}

	return $is_show_btn;
}

/**
 * 获取一个table列表按钮html内容
 * @date: 2017-8-31 下午11:58:21
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function getTableListBtn($btn,$vo){
	$is_show_btn = true;
	if( isset($btn['show_map']) && $btn['show_map'] ){
		$show_map = $btn['show_map'];
		$is_show_btn = getMapShowRet($vo,$show_map);
	}
	if( !$is_show_btn ){
		return '';
	}

	$href = get_turl_replace($vo,$btn);
	$btnHtml = <<<str
	<a class="btn btn-xs {$btn['item_class']} mb5" href="{$href}" {$btn['attr_str']} title="{$btn['title']}"><i class="{$btn['icon_class']}"></i> {$btn['title']}</a>&nbsp;
str;

	replaceVoFields($vo,$btnHtml);
	return $btnHtml;
}


/**
 * 依据当前记录及配置获取要显示的值
 * @author: qw_xingzhe <qwei2013@gmail.com>
 */
function get_tval($vo,$cInfo){
	$field  = $cInfo['field'];
	$config = $cInfo['config'];

	$field  		= explode('>', $field);
	$field_end		= $field[0];
	$field_count	= count($field);

	if( !isset($vo[ $field_end ]) ){
		$val = '';
	}else{
		$val = $vo[ $field_end ];
		if( $field_count>1 ){
			for ($i=1;$i<$field_count;$i++){
				$field_end = $field[$i];
				$val = $val[ $field_end ];
			}
		}
	}
	$original_val = $val;

	//获取指定表的指定字段的值，使用 | 分割表与表字段
	if( $config['table_field'] ){
		$table_field = explode('|', $config['table_field']);
		//对查询出来的结果缓存2秒
		$key 		= md5($val . $config['table_field']);
		$ret_val 	= cache($key);
		if( !$ret_val ){
			$ret_val = db($table_field[0])->where( ['id'=>$val] )->value($table_field[1]);
			cache($key,$ret_val,3);
		}
		//return $ret_val;
		$val = $ret_val;
	}
	$val = get_func_val($val,$config);

	//--------------------------------------------
	$data_source_arr	= [];
	$data 				= [];
	$key_relevance 		= 'id|name';	// 键|值
	$data_use_type 		= '';
	if( (isset($config['config_name']) && !empty($config['config_name'])) || (isset($config['listdata']) && !empty($config['listdata'])) ){

		// 从配置中读取数组
		if( isset($config['config_name']) && !empty($config['config_name']) ){		// 使用配置数据(关联数组)
			$data_use_type 	= 'config';
			$data 			= config($config['config_name']);
		}elseif( isset($config['listdata']) && !empty($config['listdata']) ){				// 指定数据源（索引数组）
			$data_use_type 	= 'data';
			$data 			= $config['listdata'];
			$key_relevance 	= $config['key_relevance'];
		}

		$key_relevance = explode('|', $key_relevance);
		$data_key_field = $key_relevance[0];
		$data_val_field = $key_relevance[1];
		if( $data_use_type == 'data' ){
			if( isset($data[$val]) ){
				$val = $data[$val][$data_val_field];
			}else if( isset($config['data_empty']) ){
				$val = $config['data_empty'];
			}
		}

		if( !empty($data) ){
			foreach ($data as $key=>$one){
				if( is_array($one) ){
					$value 	= $one[ $data_key_field ];
					$text	= $one[ $data_val_field ];
				}else{
					$value	= $key;
					$text	= $one;
				}
				//xedit使用
				$data_source_arr[] = ['value'=>$value,'text'=>$text];
			}
		}
	}
	//--------------------------------------------
	// 	vd($cInfo);

	// xedit相关特殊处理
	if( isset($cInfo['xedit']) && !empty($cInfo['xedit']) ){
		$data_source = '';
		$append_span_class = '';
		switch ( $cInfo['xedit'] ){
			case 'select':
				$data_source_arr = json_encode($data_source_arr);
				$data_source_arr = str_replace('"', "'", $data_source_arr);
				//$data_source = "[{value: 1, text: 'Male'},{value: 2, text: 'Female'}]";
				$data_source = 'data-source="'.$data_source_arr.'"';
				break;
			case 'tags':
				$cInfo['xedit'] 	= 'select2';
				$append_span_class 	.= 'xedit-tags';
				//$data_source = "data-source='[{id: 1, text: 1},{id: 2, text: 3}]'";
				break;
		}
		$val = '<span class="xedit '.$append_span_class.'" data-value="'.$original_val.'" data-pk="'.$vo['id'].'" '.$data_source.' data-name="'.$field_end.'" data-type="'.$cInfo['xedit'].'">'.$val.'</span>';
	}

	// 前置与后置内容
	//--------------------------------------------
	$prepend_content 	= getAttachContent($vo,$cInfo,'prepend_content');
	$append_content 	= getAttachContent($vo,$cInfo,'append_content');

	// 底部追加内容
	//--------------------------------------------
	$bottom_html 		= getAttachContent($vo,$cInfo,'bottom_html');
	// 	vd($prepend_content);vde($bottom_html);
	if( !empty($bottom_html) ){
		$bottom_html = '<div class="bottom-box">' . $bottom_html . '</div>';
	}
	//$bottom_html = '';
	// 	if( !empty($config['bottom_html']) ){
	// 		$bottom_html = '<div class="bottom-box">' . $config['bottom_html'] . '</div>';
	// 	}

	// 替换字符变量
	//--------------------------------------------
	foreach ($vo as $field=>$value){
		if( is_array($field) || is_array($value) )continue;
		if( $bottom_html ){
			$bottom_html 	 = str_replace('{{$'.$field.'}}', $value, $bottom_html);
			$bottom_html 	 = str_replace('##$'.$field.'##', $value, $bottom_html);
		}
		if( $prepend_content ){
			$prepend_content = str_replace('{{$'.$field.'}}', $value, $prepend_content);
			$prepend_content = str_replace('##$'.$field.'##', $value, $prepend_content);
		}
		if( $append_content ){
			$append_content  = str_replace('{{$'.$field.'}}', $value, $append_content);
			$append_content  = str_replace('##$'.$field.'##', $value, $append_content);
		}
	}

	return $prepend_content . $val . $append_content . $bottom_html;
}
/**
 * 替换字符串内的变量
 */
function replaceVoFields($vo,&$str){
	foreach ($vo as $field=>$value){
		replaceOneFields($field,$value,$str);
	}
}
function replaceOneFields($field,$value,&$str){
	if( is_array($field) || is_array($value) )return;
	if( $str ){
		$str  = str_replace('{{$'.$field.'}}', $value, $str);
	}
}





function getAttachContent($vo,$cInfo,$key){
	$attach_content = $cInfo['config'][ $key ];
	// 依据匹配项来控制是否显示
	if( is_array($attach_content) && isset($attach_content['show_map']) ){
		$is_show = getMapShowRet($vo,$attach_content['show_map']);

		// 		vd($is_show);vd($vo);vde($attach_content['show_map']);

		if( is_array($attach_content['content']) ){	// 内容为索引数组，第一项为匹配到显示的，第二项为未匹配到显示的
			$attach_content = ($is_show)?$attach_content['content'][0]:$attach_content['content'][1];
		}else if($is_show){
			$attach_content = $attach_content['content'];
		}else{
			$attach_content = '';
		}
	}
	return $attach_content;
}


/**
 * 格式化textarea内容至html显示格式
 * @param unknown $content
 * @return string
 */
function formatTextContent($content){
	$pattern = array(
			'/ /',//半角下空格
			'/　/',//全角下空格
			//'/\r\n/',//window 下换行符
			//'/\n/',//Linux && Unix 下换行符
	);
	$replace = array('&nbsp;','&nbsp;');	//,'<br />','<br />'
	$content = preg_replace($pattern, $replace, $content);
	$content = nl2br($content);

	return $content;
}

/**
 * 二维数组排序
 * @param unknown $arr	要排序的数组
 * @param unknown $key	要排序的key
 * @param unknown $sort_order	排序类型：SORT_DESC SORT_ASC
 */
function arrSort(&$arr,$key,$sort_order=SORT_ASC){
	$keys = [];
	foreach ($arr as $one) {
		$keys[] = $one[$key];
	}
	array_multisort($keys, $sort_order, $arr);
}



/**
 *
 * 2018-1-10 上午9:27:51
 * qw_xingzhe <qwei2013@gmail.com>
 */
function getQrPng($url, $size=6, $level='M', $outfile = false)
{
	// 纠错级别：L、M、Q、H
	//$level = 'L';
	// 点的大小：1到10,用于手机端4就可以了
	//$size = 6;
	// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
	//$path = "images/";
	// 生成的文件名
	//$fileName = $path.$size.'.png';

	// 清除之前的所有输出
	ob_end_clean();

	$margin = 1;
	\Kollway\Phpqrcode\QRcode::png($url, $outfile, $level, $size, $margin);
}

/**
 * 生成唯一码 UUID
 * @param string $prefix
 * @return string
 */
function create_uuid($prefix = ""){    //可以指定前缀
	$str = md5(uniqid(mt_rand(), true));
	$uuid  = substr($str,0,8) . '-';
	$uuid .= substr($str,8,4) . '-';
	$uuid .= substr($str,12,4) . '-';
	$uuid .= substr($str,16,4) . '-';
	$uuid .= substr($str,20,12);
	return $prefix . $uuid;
}


function whale_url($url = '', $vars = [], $suffix = true, $domain = false){

	$param = request()->param();
	if( isset($param['use_type']) ){			// 当前数据使用类型
		$vars['use_type'] = $param['use_type'];
	}
	if( isset($param['modal_show']) ){			// 模态框展示
		$vars['modal_show'] = $param['modal_show'];
	}
	if( isset($param['modal_jump_type']) ){		// 模态框外跳转类型
		$vars['modal_jump_type'] = $param['modal_jump_type'];
	}


	return url($url, $vars, $suffix, $domain);
}

function jump($url){
	echo "<script language='javascript' type='text/javascript'>";
	echo "window.location.href='$url'";
	echo "</script>";
	exit();
}

function whale_divided($a,$b){
	return ($a==0)?0:$a/$b;
}

function trimBr($str){
	$_rep_str = '####';
	$str = str_replace('<br />', $_rep_str, $str);
	$str = str_replace('<br/>', $_rep_str, $str);
	$str = str_replace('<br>', $_rep_str, $str);
	$str = str_replace('<br >', $_rep_str, $str);
	$str = trim($str,$_rep_str);

	$str = str_replace($_rep_str, '<br/>', $str);


	return $str;
}


function ch_num($num,$mode=true) {
	$char = array("零","一","二","三","四","五","六","七","八","九");
	$dw = array("","十","百","千","","万","亿","兆");
	$dec = "点";
	$retval = "";
	if($mode)
		preg_match_all("/^0*(\d*)\.?(\d*)/",$num, $ar);
	else
		preg_match_all("/(\d*)\.?(\d*)/",$num, $ar);
	if($ar[2][0] != "")
		$retval = $dec . ch_num($ar[2][0],false); //如果有小数，先递归处理小数
	if($ar[1][0] != "") {
		$str = strrev($ar[1][0]);
		for($i=0;$i<strlen($str);$i++) {
			$out[$i] = $char[$str[$i]];
			if($mode && $i>0) {
				$out[$i] .= $str[$i] != "0"? $dw[$i%4] : "";
				if($str[$i]+$str[$i-1] == 0)
					$out[$i] = "";
				if($i%4 == 0)
					$out[$i] .= $dw[4+floor($i/4)];
			}
		}
		$retval = join("",array_reverse($out)) . $retval;
	}
	return $retval;
}




/**
 *  封装HTML标签
 * @param string $html html源码
 * @return string
 */
function CloseTags($html) {

	// strip fraction of open or close tag from end (e.g. if we take first x characters, we might cut off a tag at the end!)
	$html = preg_replace('/<[^>]*$/', '', $html); // ending with fraction of open tag

	// put open tags into an array
	preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	$opentags = $result[1];

	// put all closed tags into an array
	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closetags = $result[1];

	$len_opened = count($opentags);

	// if all tags are closed, we can return
	if (count($closetags) == $len_opened) {
		return $html;
	}

	// close tags in reverse order that they were opened
	$opentags = array_reverse($opentags);

	// self closing tags
	$sc = array('br', 'input', 'img', 'hr', 'meta', 'link');
	// ,'frame','iframe','param','area','base','basefont','col'
	// should not skip tags that can have content inside!

	for ($i = 0; $i < $len_opened; $i++) {
		$ot = strtolower($opentags[$i]);

		if (!in_array($opentags[$i], $closetags) && !in_array($ot, $sc)) {
			$html .= '</' . $opentags[$i] . '>';
		} else {
			unset($closetags[array_search($opentags[$i], $closetags)]);
		}
	}

	return $html;

}


function isMobile2222222()
{

	//ee($_SERVER['HTTP_USER_AGENT']);
	if(strpos($_SERVER['HTTP_USER_AGENT'],"iPad")){		//pad直接显示PC界面
		return false;
	}

	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
		return true;
	}
	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ($_SERVER['HTTP_VIA']))
	{
		// 找不到为flase,否则为true
		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	}
	// 脑残法，判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT']))
	{
		$clientkeywords = array ('nokia',
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
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true;
		}
	}
	// 协议法，因为有可能不准确，放到最后判断
	if (isset ($_SERVER['HTTP_ACCEPT']))
	{
		// 如果只支持wml并且不支持html那一定是移动设备
		// 如果支持wml和html但是wml在html之前则是移动设备
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
		{
			return true;
		}
	}
	return false;
}




function cword($data,$fileName='')
{
	if(empty($data)) return '';

	$data = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">'.$data.'</html>';
	/*
	 $dir  = "./docfile/".date("Ymd")."/";

	if(!file_exists($dir)) mkdir($dir,777,true);

	if(empty($fileName))
	{
	$fileName=$dir.date('His').'.doc';
	}
	else
	{
	$fileName =$dir.$fileName.'.doc';
	}
	*/
	$fileName = iconv('UTF-8','GB2312',$fileName);
	$writefile = fopen($fileName,'wb') or die("创建文件失败"); //wb以二进制写入
	fwrite($writefile,$data);
	fclose($writefile);
	return $fileName;
}

function dl_file($file)
{
	$file = iconv('UTF-8','GB2312',$file);

	if (!is_file($file))
	{
		die ( "<b>404 File not found!</b>" );
	}

	// Gather relevent info about file
	$len = filesize ( $file );
	$filename = basename ( $file );
	$file_extension = strtolower ( substr ( strrchr ( $filename, "." ), 1 ) );

	// This will set the Content-Type to the appropriate setting for the file
	switch ($file_extension)
	{
		case "pdf" :
			$ctype = "application/pdf";
			break;
		case "exe" :
			$ctype = "application/octet-stream";
			break;
		case "zip" :
			$ctype = "application/zip";
			break;
		case "doc" :
			$ctype = "application/msword";
			break;
		case "xls" :
			$ctype = "application/vnd.ms-excel";
			break;
		case "ppt" :
			$ctype = "application/vnd.ms-powerpoint";
			break;
		case "gif" :
			$ctype = "image/gif";
			break;
		case "png" :
			$ctype = "image/png";
			break;
		case "jpeg" :
		case "jpg" :
			$ctype = "image/jpg";
			break;
		case "mp3" :
			$ctype = "audio/mpeg";
			break;
		case "wav" :
			$ctype = "audio/x-wav";
			break;
		case "mpeg" :
		case "mpg" :
		case "mpe" :
			$ctype = "video/mpeg";
			break;
		case "mov" :
			$ctype = "video/quicktime";
			break;
		case "avi" :
			$ctype = "video/x-msvideo";
			break;

			// The following are for extensions that shouldn't be downloaded
			// (sensitive stuff, like php files)
		case "php" :
		case "htm" :
		case "html" :
		case "txt" :
			die ( "<b>Cannot be used for " . $file_extension . " files!</b>" );
			break;

		default :
			$ctype = "application/force-download";
	}


	$file_temp = fopen ( $file, "r" );


	// Begin writing headers
	header ( "Pragma: public" );
	header ( "Expires: 0" );
	header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	header ( "Cache-Control: public" );
	header ( "Content-Description: File Transfer" );
	// Use the switch-generated Content-Type
	header ( "Content-Type: $ctype" );
	// Force the download
	$header = "Content-Disposition: attachment; filename=" . $filename . ";";
	header ( $header );
	header ( "Content-Transfer-Encoding: binary" );
	header ( "Content-Length: " . $len );


	//@readfile ( $file );
	echo fread ( $file_temp, filesize ( $file ) );
	fclose ( $file_temp );

	exit ();
}


function addFileToZip($path,$zip){
	$handler=opendir($path); //打开当前文件夹由$path指定。
	while(($filename=readdir($handler))!==false){
		if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
			if(is_dir($path.DS.$filename)){// 如果读取的某个对象是文件夹，则递归
				//addFileToZip($path."/".$filename, $zip);
			}else{ //将文件加入zip对象
				$zip->addFile($path.DS.$filename);
				$zip->renameName($path.DS.$filename,$filename);
			}
		}
	}
	@closedir($path);
}
function deldir($dir) {
	//先删除目录下的文件：
	$dh=opendir($dir);
	while ($file=readdir($dh)) {
		if($file!="." && $file!="..") {
			$fullpath=$dir.DS.$file;
			if(!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				deldir($fullpath);
			}
		}
	}
	closedir($dh);
	//删除当前文件夹：
	if(rmdir($dir)) {
		return true;
	} else {
		return false;
	}
}















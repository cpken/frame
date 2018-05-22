<?php
/*
 * 框架公共函数库
 * */

/*
 * 格式化打印code
 * mixed $val
 * */
function p($val=''){
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}//p() end

/*
 * 加载系统配置文件
 * 配置目录 CORE_PATH/config/default.inc.php
 * string $fileName 配置文件名
 * */
function config($fileName=''){
    $file = CORE_PATH.'/config/'.$fileName.'.inc.php';
    if( is_file($file) ){
        $inc = include $file;
        return $inc;
    }else{
        $message = '未找到[配置文件] '.$file;
        \core\library\log::write($message);
        \core\library\error::show(404, $message);
        die;
    }
}//config() end

//将字符串按一定的规则加密
//string $_str 需要加密的字符串
//string $_key 密钥
//return string
function transcode($_str, $_key){
    $j = 0;
    $j2 = 0;
    for($k = 0; $k < strlen($_str); $k++){
        if($j2 * 7 / 10 % 10 != 0) {
	    $_str[$k] = $_str[$k] ^ $_key[$j];
	}else{
	    $_str[$k] = ~($_str[$k]);
        }
	$j++;
	$j2++;
	if($j == strlen($_key)){
	   $j = 0;
	}
    }
    return $_str;
}/*transcode() end */

/*
* 按一定的规则对字符进行编码
* string $_num
* return string
* 解码请调用 decode_endian()
*/
function encode_endian( $_num ){
    $_str = '0000';
    //此处需要将得到的数字转换为对应的字符(ascii)
    $_str[0] = chr(($_num >> 24) & 0xFF);
    $_str[1] = chr(($_num >> 16) & 0xFF);
    $_str[2] = chr(($_num >> 8 ) & 0xFF);
    $_str[3] = chr(($_num >> 0 ) & 0xFF);
     
    return $_str;
}/*encode_endian() end */

/*
* 解码 encode_endian() 加密后的内容
* string $_str
* return string
*/
function decode_endian( $_str ){
    $_ret = 0;
    $_ret = ($_ret << 8) | ord($_str[0]);
    $_ret = ($_ret << 8) | ord($_str[1]);
    $_ret = ($_ret << 8) | ord($_str[2]);
    $_ret = ($_ret << 8) | ord($_str[3]);
     
    return $_ret;
}/*decode_endian() end */


<?php
namespace Docbox\utils;

include_once(dirname(__FILE__) . "/Utils.php");
/**
 * TODO Incluir Date e Checkbox
 * 
 * @author ailton
 */
class Input {
    static function int($key) {
        return Input::postInt($key);
    }
    /**
     * String do $_POST
     * 
     * @param string $key
     * @return string
     */
    static function str($key) {
        return Input::postStr($key);
    }

    static function getInt($key) {
        return isset($_GET[$key]) ? intval($_GET[$key]) : 0;
    }
    
    static function getStr($key) {
        $str = isset($_GET[$key]) ? $_GET[$key] : NULL;
        if($str != NULL) {
            if (get_magic_quotes_gpc()) {
                $str = stripslashes($str);
            }
            $str = cleanInput($str);
        }
        return utf8_decode($str);
    }
    
    static function getBoolean($key) {
        return isset($_GET[$key]) && strcasecmp($_GET[$key], "true") == 0;
    }
    
    static function boolean($key) {
        return isset($_POST[$key]) && strcasecmp($_POST[$key], "true") == 0;
    }
    
    static function postInt($key) {
        return isset($_POST[$key]) ? intval($_POST[$key]) : 0;
    }
    
    static function postStr($key) {
        $str = isset($_POST[$key]) ? $_POST[$key] : NULL;
        if($str != NULL) {
            /*if (get_magic_quotes_gpc()) {
                $str = stripslashes($str);
            }*/
            $str = cleanInput($str);
        }
        return utf8_decode($str);
    }
}
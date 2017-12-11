<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2017/12/4
 * Time: 20:26
 */
namespace core\common;

class Functions {
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /*
     * 返回输入数组中某个单一列的值, array_column方法 兼容5.5之前版本，
     * @access public
     * @param array     $array 规定要使用的多维数组(记录集)
     * @param string    $columnKey 需要返回值的列
     * @param string    $indexKey 用作返回数组的索引/键的列(可选)
     * @return array 输入数组中某个单一列的值
     */
    public static function vg_array_column($array, $columnKey, $indexKey = null) {
        if(!function_exists('array_column')){
            $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
            $indexKeyIsNull = (is_null($indexKey)) ? true : false;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;

            $result = array();
            foreach((array)$array as $key=>$row){
                if($columnKeyIsNumber) {
                    $tmp= array_slice($row, $columnKey, 1);
                    $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
                }
                else {
                    $tmp= isset($row[$columnKey]) ? $row[$columnKey] : null;
                }

                if(!$indexKeyIsNull){
                    if($indexKeyIsNumber){
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key))?current($key):null;
                        $key = is_null($key)?0:$key;
                    }else{
                        $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                    }
                }
                $result[$key] = $tmp;
            }
            return $result;
        }else{
            return array_column($array, $columnKey, $indexKey);
        }
    }
}
<?php
function allow2Text($str) {
	return preg_match('/[0-9A-Za-z\-\s,]+/', $str) ? $str : '';
}
function object2array(&$object) {
    if (is_object($object)) {
        $arr = (array)($object);
    } else {
        $arr = &$object;
    }
    if (is_array($arr)) {
        foreach($arr as $varName => $varValue){
            $arr[$varName] = object2array($varValue);
        }
    }
    return $arr;
}
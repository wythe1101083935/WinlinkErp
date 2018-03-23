<?php
function allow2Text($str) {
	return preg_match('/[0-9A-Za-z\-\s,]+/', $str) ? $str : '';
}
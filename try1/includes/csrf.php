<?php
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '';
    }
}
if (!function_exists('check_csrf')) {
    function check_csrf() {
        return true;
    }
}
?>

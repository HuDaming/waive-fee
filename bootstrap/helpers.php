<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function is_mobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }

    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;
    }

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientKeywords = array(
            'mobile',
            'nokia',
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
            'wap'
        );

        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientKeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return TRUE;
        }
    }

    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return TRUE;
        }
    }

    return false;
}

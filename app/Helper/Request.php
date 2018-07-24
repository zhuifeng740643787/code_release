<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/28
 * Time: 下午3:51
 */

namespace App\Helper;

class Request
{

    public static function post($url, $params = [], $headers = [], $get_header = false)
    {
        return self::send('POST', $url, $params, $headers, $get_header);
    }

    public static function get($url, $params = [], $headers = [], $get_header = false)
    {
        return self::send('GET', $url, $params, $headers, $get_header);
    }

    public static function send($method, $url, $params = [], $headers = [], $get_header = false)
    {
        $method = strtoupper($method);
        if (is_array($params) && $method === 'GET') {
            $str = '';
            foreach ($params as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }
                $str .= "$key=$value&";
            }
            $params = rtrim($str, '&');
        }
        if ($method === 'GET') {
            $url .= strpos($url, '?') > 0 ? $params : '?' . $params;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, $get_header);// 返回响应头
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if (strpos($response, '404 Not Found') > 0) {
            return false;
        }

        return $response;
    }


}
<?php

namespace Hhxsv5\PhpMultiCurl;

class Curl
{
    protected $handle;

    protected $response;

    protected $multi = false;

    protected $logPath;

    protected static $defaultOptions = [
        //启用时会将头文件的信息作为数据流输出
        CURLOPT_HEADER         => false,
        //禁止 cURL 验证对等证书
        CURLOPT_SSL_VERIFYPEER => false,
        //将会根据服务器返回 HTTP 头中的 "Location: " 重定向
        CURLOPT_FOLLOWLOCATION => true,
        //获取的信息以字符串返回，而不是直接输出
        CURLOPT_RETURNTRANSFER => true,

        //Location重定向最大次数
        CURLOPT_MAXREDIRS      => 3,
        ////设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配，0不检查
        CURLOPT_SSL_VERIFYHOST => 0,
        //允许 cURL 函数执行的最长秒数
        CURLOPT_TIMEOUT        => 6,
        //在尝试连接时等待的秒数。设置为0，则无限等待
        CURLOPT_CONNECTTIMEOUT => 3,
        //在HTTP请求中包含一个"User-Agent: "头的字符串
        CURLOPT_USERAGENT      => 'PHP Multi Curl Client V1.0',
    ];

    public function __construct(array $options = [])
    {
        $this->handle = curl_init();
        $finalOptions = $options + self::$defaultOptions;
        curl_setopt_array($this->handle, $finalOptions);
    }

    public function makeGet($url, $params = null, array $headers = [])
    {
        if (is_string($params) || is_array($params)) {
            is_array($params) AND $params = http_build_query($params);
            $url = rtrim($url, '?');
            if (strpos($url, '?') !== false) {
                $url .= '&' . $params;
            } else {
                $url .= '?' . $params;
            }
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPGET, true);//HTTP GET
        $headers AND curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        if ($this->logPath) {
//            $f = fopen($this->logPath, 'w+');
//            curl_setopt($this->handle, CURLOPT_VERBOSE, true);
//            curl_setopt($this->handle, CURLOPT_STDERR, $f);
            $logContent = sprintf('[CURL][GET: %s]%s', $url, PHP_EOL);
            file_put_contents($this->logPath, $logContent, FILE_APPEND);
        }
    }

    public function makePost($url, $params = null, array $headers = [])
    {
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_POST, true);//HTTP POST

        if (is_string($params) || is_array($params)) {
            is_array($params) AND $params = http_build_query($params);
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $params);
        }

        $headers AND curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        if ($this->logPath) {
//            $f = fopen($this->logPath, 'w+');
//            curl_setopt($this->handle, CURLOPT_VERBOSE, true);
//            curl_setopt($this->handle, CURLOPT_STDERR, $f);
            $logContent = sprintf('[CURL][POST: %s,%s]%s', $url, $params, PHP_EOL);
            file_put_contents($this->logPath, $logContent, FILE_APPEND);
        }
    }

    public function exec()
    {
        $this->response = curl_exec($this->handle);

        if ($errno = curl_errno($this->handle)) {
            if ($this->logPath) {
                $info = curl_getinfo($this->handle);
                $logContent = sprintf('[CURL][URL: %s][ERROR: %s,%s]%s', $info['url'], $errno, curl_error($this->handle), PHP_EOL);
                file_put_contents($this->logPath, $logContent, FILE_APPEND);
            }
            $this->response = false;
        }

        return $this->response;
    }

    public function setMulti($isMulti)
    {
        $this->multi = (bool)$isMulti;
    }

    public function getResponse()
    {
        if ($this->response !== null) {
            return $this->response;
        }

        if ($this->multi) {
            $this->response = curl_multi_getcontent($this->handle);
        }

        return $this->response;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }
}
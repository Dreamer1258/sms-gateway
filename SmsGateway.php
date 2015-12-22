<?php

namespace Dreamer1258\SmsGateway;

use Dreamer1258\SmsGateway\Exception\SmsGatewayException;

class SmsGateway
{
    public function __construct()
    {
        if(!function_exists('curl_version')) {
            throw new SmsGatewayException('Unable to find cURL!');
        }
    }

    public function send($number, $message)
    {
        $ch = $this->initCurl();
        $phpsessid = $this->getHtmlProperty(
            $this->sendCurlRequest($ch, 'http://darmowabramkasms.net/index.php?page=sendsms', array(
            'phoneno' => $number,
                'message' => $message,
                'action' => 'verify',
                'ads_check1' => 'js_off',
        'ads_check2' => 'js_off'
        )),
            'PHPSESSID'
        );

        $this->sendCurlRequest($ch, 'http://darmowabramkasms.net/index.php', array(
            'PHPSESSID' => $phpsessid,
            'action' => 'confirmbyuser'
        ));

        $imagecode = $this->getHtmlProperty(
            $this->sendCurlRequest($ch, 'http://darmowabramkasms.net/index.php', array(
                'operator' => 'play',
                'action' => 'confirmprovider'
            )),
            'imgcode'
        );

        $this->sendCurlRequest($ch, 'http://darmowabramkasms.net/index.php?a=sent', array(
            'imgcode' => $imagecode,
            'action' => 'useraccepted'
        ));

        $this->closeCurl($ch);

        return true;
    }

    public function sendMultiple(array $numbers, $message)
    {
        $sent = 0;

        foreach ($numbers as $number) {
            $sent += $this->send($number, $message);
            sleep(1);
        }

        return $sent;
    }

    protected function initCurl()
    {
        $ch = curl_init();
        $cookie_file = '/tmp/' . date('d') . '_cookie.txt';
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => $cookie_file,
            CURLOPT_COOKIEFILE => $cookie_file
        ));

        return $ch;
    }

    protected function closeCurl($ch)
    {
        if ($ch) {
            curl_close($ch);
        }
    }

    protected function sendCurlRequest($ch, $url, array $params)
    {
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => http_build_query($params)
        ));

        $response = curl_exec($ch);
        $error = curl_error($ch);

        if ($error) {
            throw new SmsGatewayException($error);
        }

        return $response;
    }

    protected function getHtmlProperty($html, $property)
    {
        if (!preg_match('/name=\"' . $property . '\".*?value=\"([^\"]+)\"/', $html, $matches)) {
            preg_match('/value=\"([^\"]+)\".*?name=\"' . $property . '\"/', $html, $matches);
        }

        return isset($matches[1]) ? $matches[1] : null;
    }
}
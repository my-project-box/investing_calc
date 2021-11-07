<?php
namespace library;

/**
 * 
 * 
 */
class Curl 
{
    private $referer = 'https://www.google.ru/';
    
    private $userAgents = [
        // Opera
        'Mozilla / 5.0 (Windows NT 10.0; Win64; x64) AppleWebKit / 537.36 (KHTML, как Gecko) Chrome / 80.0.3987.163 Safari / 537.36 OPR / 67.0.3575.137',
        'Mozilla / 5.0 (Windows NT 10.0; Win64; x64) AppleWebKit / 537.36 (KHTML, как Gecko) Chrome / 80.0.3987.149 Safari / 537.36 OPR / 67.0.3575.115',

        // Mozilla Firefox
        'Mozilla / 5.0 (Macintosh; Intel Mac OS X 10.14; rv: 75.0) Gecko / 20100101 Firefox / 75.0',
        'Mozilla / 5.0 (Windows NT 6.1; Win64; x64; rv: 74.0) Gecko / 20100101 Firefox / 74.0',
        'Mozilla / 5.0 (X11; Ubuntu; Linux x86_64; rv: 75.0) Gecko / 20100101 Firefox / 75.0',
        'Mozilla / 5.0 (Windows NT 10.0; rv: 68.0) Gecko / 20100101 Firefox / 68.0',

        // Google Chrome
        'Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit / 537.36 (KHTML, как Gecko) Chrome / 80.0.3987.163 Safari / 537.36',
        'Mozilla / 5.0 (Windows NT 10.0; Win64; x64) AppleWebKit / 537.36 (KHTML, как Gecko) Chrome / 81.0.4044.92 Safari / 537.36',
        'Mozilla / 5.0 (Windows NT 6.1; Win64; x64) AppleWebKit / 537.36 (KHTML, как Gecko) Chrome / 80.0.3987.163 Safari / 537.36',

        // Safari
        'Mozilla / 5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit / 605.1.15 (KHTML, как Gecko) Версия / 13.1 Safari / 605.1.15'
    ];
    


    /**
     * Получаем контент сайта-донора
     * 
     * 
     */
    public function query($url, $post = '') 
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_REFERER        => $this->referer,
            CURLOPT_USERAGENT      => $this->getUserAgent(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_POST           => isset($post) ? true : false,
            //CURLOPT_POSTFIELDS     => isset($post) ? $post : '',
            CURLOPT_TIMEOUT        => 60
        ]);

        $result = curl_exec($curl);
        $info   = curl_getinfo($curl);

        curl_close($curl);

        return ['result' => $result, 'info' => $info];
    }



    /**
     * Получаем Юзер агент рандомно из массива
     * 
     */
    private function getUserAgent() {
        return array_rand($this->userAgents);
    }



    /**
     * Проверяем домен на существование
     * 
     */
    public function checkDomain($url) 
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_HEADER        => true,
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $curl_result      = curl_exec($curl);
        $curl_info        = curl_getinfo($curl);
        $curl_error_code  = curl_errno($curl);

        curl_close($curl);

        return $curl_error_code;
    }


} // class Curl
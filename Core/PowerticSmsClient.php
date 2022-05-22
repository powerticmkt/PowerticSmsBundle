<?php

/**
 * @copyright   2022 Powertic. All rights reserved
 * @author      Luiz Eduardo Oliveira Fonseca <luizeof@gmail.com>
 *
 * @link        https://powertic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\PowerticSmsBundle\Core;

class PowerticSmsClient
{

    private $url;
    private $apikey;

    public function __construct($apikey, $url)
    {
        $this->apikey = $apikey;
        $this->url = $url;
    }

    public function post($msg)
    {
        $curl = curl_init();

        $headers = array(
            'Content-Type: application/json',
            'X-API-TOKEN: ' . $this->apikey
        );

        curl_setopt($curl, CURLOPT_URL, $this->url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($msg));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}

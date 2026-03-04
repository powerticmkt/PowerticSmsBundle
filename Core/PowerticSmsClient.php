<?php

declare(strict_types=1);

namespace MauticPlugin\PowerticSmsBundle\Core;

class PowerticSmsClient
{
    public function __construct(
        private string $apikey,
        private string $url,
    ) {}

    /**
     * @param array<string, mixed> $msg
     */
    public function post(array $msg): string|false
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
        ];

        if (!empty($this->apikey)) {
            $headers[] = 'X-API-TOKEN: ' . $this->apikey;
        }

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

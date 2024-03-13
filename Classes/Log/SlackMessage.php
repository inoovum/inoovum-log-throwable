<?php
namespace Inoovum\Log\Throwable\Log;

use GuzzleHttp\Client;
use Neos\Flow\Annotations as Flow;

class SlackMessage
{

    /**
     * @param string $errorInfo
     * @param array $options
     * @return void
     */
    public function throwError(string $errorInfo, array $options): void
    {
        $client = new Client();
        $url = $options['webhookUri'];
        $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'text' => $errorInfo
            ]
        ]);
    }

}

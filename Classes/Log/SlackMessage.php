<?php
namespace Inoovum\Log\Throwable\Log;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Neos\Flow\Annotations as Flow;

class SlackMessage implements ThrowableInterface
{

    /**
     * @param string $errorInfo
     * @param array $options
     * @return void
     * @throws GuzzleException
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

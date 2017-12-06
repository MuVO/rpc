<?php namespace MuVO\RPC;

use GuzzleHttp\Client;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class XML
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(string $rpcUri)
    {
        $this->logger = new Logger('XML-RPC');
        $this->client = new Client([
            'base_uri' => $rpcUri,
            'verify' => false,
        ]);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $request = xmlrpc_encode_request($name, $arguments);
        $this->logger->debug($request);

        $response = $this->client->post('.', ['body' => $request]);
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning($response->getReasonPhrase());
        } elseif ($body = $response->getBody()->__toString()) {
            $this->logger->debug($body);
            return xmlrpc_decode($body);
        }

        throw new \Error(sprintf("Can't call «%s» on remote API", $name), $response->getStatusCode());
    }
}

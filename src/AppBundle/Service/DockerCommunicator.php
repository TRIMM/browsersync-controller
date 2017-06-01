<?php
namespace AppBundle\Service;

use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;

class DockerCommunicator
{

    private $client;

    private $messageFactory;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
        $this->messageFactory = MessageFactoryDiscovery::find();
    }

    /**
     * @return mixed
     */
    public function listContainers()
    {
        return $this->get('/containers/json?all=true');
    }

    /**
     * @param object|string $container
     */
    public function killContainer($container)
    {
        if(is_object($container)) {
            $container = $container->Id;
        }
        $this->post('/containers/' . $container . '/kill');
    }

    /**
     * @param object|string $container
     */
    public function removeContainer($container)
    {
        if(is_object($container)) {
            $container = $container->Id;
        }
        $this->delete('/containers/' . $container);
    }

    /**
     * @param object|string $container
     */
    public function startContainer($container)
    {
        if(is_object($container)) {
            $container = $container->Id;
        }
        $this->post('/containers/' . $container . '/start');
    }


    /**
     * @param $name
     * @param $options
     * @return mixed|string
     */
    public function createContainer($name, $options)
    {
        return $this->post('/containers/create?name=' . $name, $options);
    }

    /**
     * @param object|string $container
     * @param $options
     * @return mixed
     */
    public function createExec($container, $options)
    {
        if(is_object($container)) {
            $container = $container->Id;
        }
        $result = $this->post('/containers/' . $container . '/exec', $options);
        return $result->Id;
    }

    public function startExec($executeId)
    {
        $this->post('/exec/' . $executeId . '/start', [
            'Detach' => true
        ]);
    }

    /**
     * @param $path
     * @return mixed
     */
    private function get($path)
    {
        $request = $this->messageFactory->createRequest('GET', $path);
        $response = $this->client->sendRequest($request);
        $body = $response->getBody()->getContents();
        return json_decode($body);
    }

    /**
     * @param $path
     * @return mixed
     */
    private function delete($path)
    {
        $request = $this->messageFactory->createRequest('DELETE', $path);
        $response = $this->client->sendRequest($request);
        $body = $response->getBody()->getContents();
        return json_decode($body);
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    private function post($path, array $data = null)
    {
        if (is_array($data)) {
            $body = json_encode($data);
            $headers = [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($body)
            ];
            $request = $this->messageFactory->createRequest('POST', $path, $headers, $body);
        } else {
            $request = $this->messageFactory->createRequest('POST', $path, [], $data);
        }
        $response = $this->client->sendRequest($request);
        $body = $response->getBody()->getContents();
        return json_decode($body);
    }
}
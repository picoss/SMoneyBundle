<?php

namespace Picoss\SMoneyBundle\DataCollector;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Picoss\SMoneyBundle\GuzzleHttp\Subscriber\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SMoneyDataCollector extends DataCollector
{
    /**
     * Profiler subscriber
     *
     * @var Profiler
     */
    private $profiler;

    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [
            'calls' => [],
            'error_count' => 0,
            'methods' => [],
            'total_time' => 0,
        ];

        /**
         * Aggregates global metrics about Guzzle usage
         *
         * @param array $request
         * @param array $response
         * @param array $time
         * @param bool $error
         */
        $aggregate = function ($request, $response, $time, $error) use (&$data) {
            $method = $request['method'];
            if (!isset($data['methods'][$method])) {
                $data['methods'][$method] = 0;
            }
            $data['methods'][$method]++;
            $data['total_time'] += $time['total'];
            $data['error_count'] += (int) $error;
        };

        $isResponseError = function ($response) {
            $code = $response->getStatusCode();
            return ($code >= 400 && $code < 500) || ($code >= 500 && $code < 600);
        };

        foreach ($this->profiler as $call) {
            /** @var RequestInterface $request */
            $request = $call['request'];
            /** @var ResponseInterface $response */
            $response = $call['response'];

            $requestData = $this->collectRequestData($request);
            $responseData = $this->collectResponseData($response);
            $timeData = $this->collectTimeData($call['info']);
            $error = $isResponseError($response);

            $aggregate($requestData, $responseData, $timeData, $error);

            $data['calls'][] = array(
                'request' => $requestData,
                'response' => $responseData,
                'time' => $timeData,
                'error' => $error
            );
        }

        $this->data = $data;
    }

    private function collectRequestData(RequestInterface $request)
    {
        return [
            'headers' => $request->getHeaders(),
            'method' => $request->getMethod(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'path' => $request->getPath(),
            'query' => $request->getQuery(),
            'body' => $request->getBody(),
        ];
    }

    private function collectResponseData(ResponseInterface $response)
    {
        return [
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'headers' => $response->getHeaders(),
            'body' => $response->getBody(),
        ];
    }

    private function collectTimeData(array $info)
    {
        return [
            'total' => $info['total_time'],
            'connection' => $info['connect_time'],
        ];
    }

    public function getName()
    {
        return 'smoney';
    }
}
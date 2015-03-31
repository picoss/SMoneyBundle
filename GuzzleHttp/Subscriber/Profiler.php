<?php

namespace Picoss\SMoneyBundle\GuzzleHttp\Subscriber;

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class Profiler implements SubscriberInterface, \IteratorAggregate
{

    /** @var array Requests and responses that have passed through the plugin */
    private $transactions = [];

    public function getEvents()
    {
        return [
            'complete' => ['onComplete', RequestEvents::LATE],
            'error' => ['onError', RequestEvents::EARLY],
        ];
    }

    public function onComplete(CompleteEvent $event)
    {
        $this->add($event->getRequest(), $event->getTransferInfo(), $event->getResponse());
    }

    public function onError(ErrorEvent $event)
    {
        // Only track when no response is present, meaning this didn't ever
        // emit a complete event
        if (!$event->getResponse()) {
            $this->add($event->getRequest(), $event->getTransferInfo());
        }
    }

    /**
     * Returns an Iterator that yields associative array values where each
     * associative array contains a 'request' and 'response' key.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->transactions);
    }

    /**
     * Add a request to the history
     *
     * @param RequestInterface $request Request to add
     * @param array $info Transfer info.
     * @param ResponseInterface $response Response of the request
     */
    private function add(
        RequestInterface $request,
        array $info = [],
        ResponseInterface $response = null
    ) {
        $this->transactions[] = [
            'request' => $request,
            'sent_request' => clone $request,
            'response' => $response,
            'info' => $info,
        ];
    }

}
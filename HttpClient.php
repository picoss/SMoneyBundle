<?php

namespace Picoss\SMoneyBundle;

use Picoss\SMoney\HttpClient as BaseHttpClient;

class HttpClient extends BaseHttpClient
{

    public function addSubscriber($subscriber)
    {
        $this->getClient()->getEmitter()->attach($subscriber);
    }

}
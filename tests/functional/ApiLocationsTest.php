<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Silex\WebTestCase;

class ApiLocationsTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../source/Bootstrap.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();
        return $app;
    }

    public function test()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/locations');
        $this->assertTrue($client->getResponse()->isOk());
    }
}
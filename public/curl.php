<?php
require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
            'base_uri' => "http://192.168.123.56:8000/API/suppliers",
            'timeout'  => 20.0,
        ]);
        
        $response = $client->get('');
        
        dump($response);
        echo $response->getBody();
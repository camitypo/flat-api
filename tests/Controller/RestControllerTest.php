<?php

namespace App\Tests\Controller;

use App\Exception\RestException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestControllerTest extends WebTestCase
{
    /**
     * Tests if create flat route returns HTTP status code 201.
     */
    public function testCreateFlatReturnsStatusCreated()
    {
        $data = [
            'occupancyDate' => date('Y-m-d'),
            'street' => md5(rand()),
            'zip'    => rand(1, 999999),
            'city'   => md5(rand()),
            'country' => md5(rand()),
            'email'   => md5(rand(1, 99999)).'@domain.tld'
        ];
        $client = static::createClient();
        $client
            ->request(
                'POST',
                '/api/v1/flats',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
            )
        ;
        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests if create flat route returns HTTP status code 400.
     */
    public function testCreateFlatReturnsStatusBadRequest()
    {
        $data = [
            'occupancyDate' => date('YYYYY-m-d')
        ];
        $client = static::createClient();
        $client
            ->request(
                'POST',
                '/api/v1/flats',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
            )
        ;
        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}

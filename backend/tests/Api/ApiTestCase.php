<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends BaseApiTestCase
{
    use ResetDatabase, Factories;

    protected function createAuthenticatedClient(string $email = 'test@example.com', string $password = 'password'): Client
    {
        UserFactory::createOne([
            'email' => $email,
            'password' => $password,
        ]);

        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        $client->setDefaultOptions([
            'auth_bearer' => $data['token'],
        ]);

        return $client;
    }
}

<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Factory\UserFactory;

class UserTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        UserFactory::createMany(5);
        $client = $this->createAuthenticatedClient();

        $response = $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users',
            '@type' => 'Collection',
            'totalItems' => 6, // 5 created + 1 from authentication
        ]);

        $this->assertCount(6, $response->toArray()['member']);
    }

    public function testGetOneUser(): void
    {
        $client = $this->createAuthenticatedClient('admin@example.com');
        $iri = $this->findIriBy(User::class, ['email' => 'admin@example.com']);

        $client->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'email' => 'admin@example.com',
        ]);
    }
}

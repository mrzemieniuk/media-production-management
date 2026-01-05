<?php

namespace App\Tests\Api;

use App\Entity\Production;
use App\Enum\ProductionStatusEnum;
use App\Factory\ProductionFactory;

class ProductionTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        ProductionFactory::createMany(5);
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/productions');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/productions',
            '@type' => 'Collection',
            'totalItems' => 5,
        ]);
    }

    public function testCreateProduction(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/productions', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'New Movie',
                'description' => 'A very interesting movie',
                'status' => ProductionStatusEnum::PLANNING->name,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'name' => 'New Movie',
            'description' => 'A very interesting movie',
            'status' => ProductionStatusEnum::PLANNING->name,
        ]);
    }

    public function testUpdateProduction(): void
    {
        $production = ProductionFactory::createOne(['name' => 'Old Title']);
        $client = $this->createAuthenticatedClient();

        $iri = $this->findIriBy(Production::class, ['id' => $production->getId()]);

        $client->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'name' => 'Updated Title',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Updated Title',
        ]);
    }

    public function testDeleteProduction(): void
    {
        $production = ProductionFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $iri = $this->findIriBy(Production::class, ['id' => $production->getId()]);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(ProductionFactory::repository()->find($production->getId()));
    }
}

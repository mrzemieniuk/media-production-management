<?php

namespace App\Tests\Api;

use App\Entity\ProductionMember;
use App\Enum\CrewPositionEnum;
use App\Factory\ProductionFactory;
use App\Factory\ProductionMemberFactory;
use App\Factory\UserFactory;

class ProductionMemberTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        ProductionMemberFactory::createMany(5);
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/production_members');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/production_members',
            '@type' => 'Collection',
            'totalItems' => 5,
        ]);
    }

    public function testCreateProductionMember(): void
    {
        $production = ProductionFactory::createOne();
        $user = UserFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/production_members', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'production' => '/api/productions/' . $production->getId(),
                'person' => '/api/users/' . $user->getId(),
                'crewPosition' => CrewPositionEnum::DIRECTOR->name,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'production' => '/api/productions/' . $production->getId(),
            'person' => '/api/users/' . $user->getId(),
            'crewPosition' => CrewPositionEnum::DIRECTOR->name,
        ]);
    }

    public function testDeleteProductionMember(): void
    {
        $member = ProductionMemberFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $iri = $this->findIriBy(ProductionMember::class, ['id' => $member->getId()]);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(ProductionMemberFactory::repository()->find($member->getId()));
    }
}

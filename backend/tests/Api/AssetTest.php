<?php

namespace App\Tests\Api;

use App\Entity\Asset;
use App\Enum\AssetStatusEnum;
use App\Enum\AssetTypeEnum;
use App\Factory\AssetFactory;
use App\Factory\ProductionFactory;

class AssetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        AssetFactory::createMany(5);
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/assets');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/assets',
            '@type' => 'Collection',
            'totalItems' => 5,
        ]);
    }

    public function testCreateAsset(): void
    {
        $production = ProductionFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/assets', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'production' => '/api/productions/' . $production->getId(),
                'type' => AssetTypeEnum::VIDEO->name,
                'filename' => 'scene1.mp4',
                'status' => AssetStatusEnum::UPLOADED->name,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'production' => '/api/productions/' . $production->getId(),
            'type' => AssetTypeEnum::VIDEO->name,
            'filename' => 'scene1.mp4',
            'status' => AssetStatusEnum::UPLOADED->name,
        ]);
    }

    public function testDeleteAsset(): void
    {
        $asset = AssetFactory::createOne();
        $client = $this->createAuthenticatedClient();

        $iri = $this->findIriBy(Asset::class, ['id' => $asset->getId()]);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(AssetFactory::repository()->find($asset->getId()));
    }
}

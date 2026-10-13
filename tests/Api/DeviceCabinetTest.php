<?php

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use Doctrine\ORM\EntityManagerInterface;

final class DeviceCabinetTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        static::getContainer()->get(EntityManagerInterface::class)->getConnection()
            ->executeStatement('TRUNCATE device, equipment_cabinet RESTART IDENTITY CASCADE');
    }

    public function testDevicesCanBeAssignedUpToMaxDevices(): void
    {
        $cabinet = $this->createCabinet(2);

        $this->createDevice($cabinet);
        $this->createDevice($cabinet);
        $this->assertResponseStatusCodeSame(201);

        $this->createDevice($cabinet);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'cabinet']]]);

        $this->assertCount(2, $this->client->request('GET', $cabinet)->toArray()['devices']);
    }

    public function testAssigningAnExistingDeviceToAFullCabinetIsRejected(): void
    {
        $full = $this->createCabinet(1);
        $this->createDevice($full);
        $device = $this->createDevice();

        $this->patch($device, ['cabinet' => $full]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'cabinet']]]);
    }

    public function testDeviceInAFullCabinetCanStillBeUpdated(): void
    {
        $cabinet = $this->createCabinet(1);
        $device = $this->createDevice($cabinet);

        $this->patch($device, ['defective' => true, 'cabinet' => $cabinet]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['defective' => true, 'cabinet' => $cabinet]);
    }

    public function testDeviceCanBeMovedAndUnassigned(): void
    {
        $from = $this->createCabinet(1);
        $to = $this->createCabinet(1);
        $device = $this->createDevice($from);

        $this->patch($device, ['cabinet' => $to]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['cabinet' => $to]);

        $this->patch($device, ['cabinet' => null]);
        $this->assertResponseIsSuccessful();
        $this->assertArrayNotHasKey('cabinet', $this->client->getResponse()->toArray());

        // The freed slot in $from is usable again.
        $this->createDevice($from);
        $this->assertResponseStatusCodeSame(201);
    }

    public function testMaxDevicesCannotBeLoweredBelowAssignedDevices(): void
    {
        $cabinet = $this->createCabinet(3);
        $this->createDevice($cabinet);
        $this->createDevice($cabinet);

        $this->patch($cabinet, ['maxDevices' => 1]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'maxDevices']]]);

        $this->patch($cabinet, ['maxDevices' => 2]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['maxDevices' => 2]);
    }

    public function testCabinetWithDevicesCannotBeDeleted(): void
    {
        $cabinet = $this->createCabinet(1);
        $device = $this->createDevice($cabinet);

        $this->client->request('DELETE', $cabinet);
        $this->assertResponseStatusCodeSame(422);

        $this->patch($device, ['cabinet' => null]);
        $this->client->request('DELETE', $cabinet);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testDevicesCannotBeAssignedThroughTheCabinet(): void
    {
        $device = $this->createDevice();

        $this->client->request('POST', '/api/equipment_cabinets', ['json' => ['maxDevices' => 1, 'devices' => [$device]]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame([], $this->client->getResponse()->toArray()['devices']);
    }

    private function createCabinet(int $maxDevices): string
    {
        return $this->client->request('POST', '/api/equipment_cabinets', ['json' => ['maxDevices' => $maxDevices]])
            ->toArray()['@id'];
    }

    private function createDevice(?string $cabinet = null): string
    {
        $response = $this->client->request('POST', '/api/devices', ['json' => [
            'type' => 'switch',
            'serialNumber' => uniqid('SN-'),
            'defective' => false,
            'manufacturer' => 'ACME',
            'cabinet' => $cabinet,
        ]]);

        return $response->toArray(false)['@id'] ?? '';
    }

    private function patch(string $iri, array $data): void
    {
        $this->client->request('PATCH', $iri, [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
    }
}

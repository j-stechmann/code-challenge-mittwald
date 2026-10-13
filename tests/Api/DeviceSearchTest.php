<?php

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use Doctrine\ORM\EntityManagerInterface;

final class DeviceSearchTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        static::getContainer()->get(EntityManagerInterface::class)->getConnection()
            ->executeStatement('TRUNCATE device, equipment_cabinet, employee RESTART IDENTITY CASCADE');

        $this->createDevice('Laptop', 'Lenovo', 'LNV-4821', false);
        $this->createDevice('Headset', 'Jabra', 'JBR-0934', false);
        $this->createDevice('Monitor', 'Dell', 'DEL-7710', true);
    }

    public function testDevicesCanBeSearchedByPartOfTheManufacturer(): void
    {
        $this->assertSame(['LNV-4821'], $this->search('manufacturer=leno'));
    }

    public function testDevicesCanBeSearchedByTypeAndSerialNumber(): void
    {
        $this->assertSame(['JBR-0934'], $this->search('type=headset'));
        $this->assertSame(['JBR-0934'], $this->search('serialNumber=JBR'));
    }

    public function testDevicesCanBeFilteredByCondition(): void
    {
        $this->assertSame(['DEL-7710'], $this->search('defective=true'));
        $this->assertSame(['LNV-4821', 'JBR-0934'], $this->search('defective=false'));
    }

    public function testFiltersCanBeCombined(): void
    {
        $this->assertSame([], $this->search('type=monitor&defective=false'));
    }

    /**
     * @return string[] the serial numbers of the matching devices
     */
    private function search(string $query): array
    {
        $members = $this->client->request('GET', '/api/devices?'.$query)->toArray()['member'];

        return array_column($members, 'serialNumber');
    }

    private function createDevice(string $type, string $manufacturer, string $serialNumber, bool $defective): void
    {
        $this->client->request('POST', '/api/devices', ['json' => [
            'type' => $type,
            'manufacturer' => $manufacturer,
            'serialNumber' => $serialNumber,
            'defective' => $defective,
        ]]);
        $this->assertResponseStatusCodeSame(201);
    }
}

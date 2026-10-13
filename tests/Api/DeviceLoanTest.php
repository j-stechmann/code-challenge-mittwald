<?php

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use Doctrine\ORM\EntityManagerInterface;

final class DeviceLoanTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        static::getContainer()->get(EntityManagerInterface::class)->getConnection()
            ->executeStatement('TRUNCATE device, equipment_cabinet, employee RESTART IDENTITY CASCADE');
    }

    public function testDeviceCanBeLoanedAndReturned(): void
    {
        $cabinet = $this->createCabinet(1);
        $device = $this->createDevice($cabinet);
        $employee = $this->createEmployee();

        $this->patch($device, ['borrower' => $employee, 'cabinet' => null]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['borrower' => $employee]);
        $this->assertSame([$device], $this->client->request('GET', $employee)->toArray()['devices']);

        $this->patch($device, ['borrower' => null, 'cabinet' => $cabinet]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['cabinet' => $cabinet]);
        $this->assertSame([], $this->client->request('GET', $employee)->toArray()['devices']);
    }

    public function testLoanedDeviceCannotStayInItsCabinet(): void
    {
        $device = $this->createDevice($this->createCabinet(1));

        $this->patch($device, ['borrower' => $this->createEmployee()]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'borrower']]]);
    }

    public function testDefectiveDeviceCannotBeLoaned(): void
    {
        $device = $this->createDevice(defective: true);

        $this->patch($device, ['borrower' => $this->createEmployee()]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'borrower']]]);
    }

    public function testDefectCanBeReportedOnReturn(): void
    {
        $device = $this->createDevice();
        $this->patch($device, ['borrower' => $this->createEmployee()]);

        $this->patch($device, ['borrower' => null, 'cabinet' => $this->createCabinet(1), 'defective' => true]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['defective' => true]);
    }

    public function testReturnIntoAFullCabinetIsRejected(): void
    {
        $full = $this->createCabinet(1);
        $this->createDevice($full);
        $device = $this->createDevice();
        $this->patch($device, ['borrower' => $this->createEmployee()]);

        $this->patch($device, ['borrower' => null, 'cabinet' => $full]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['violations' => [['propertyPath' => 'cabinet']]]);
    }

    public function testEmployeeWithLoanedDevicesCannotBeDeleted(): void
    {
        $employee = $this->createEmployee();
        $device = $this->createDevice();
        $this->patch($device, ['borrower' => $employee]);

        $this->client->request('DELETE', $employee);
        $this->assertResponseStatusCodeSame(422);

        $this->patch($device, ['borrower' => null]);
        $this->client->request('DELETE', $employee);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testDevicesCannotBeLoanedThroughTheEmployee(): void
    {
        $device = $this->createDevice();

        $this->client->request('POST', '/api/employees', ['json' => ['name' => 'Erika', 'devices' => [$device]]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame([], $this->client->getResponse()->toArray()['devices']);
    }

    private function createEmployee(): string
    {
        return $this->client->request('POST', '/api/employees', ['json' => ['name' => 'Erika Musterfrau']])
            ->toArray()['@id'];
    }

    private function createCabinet(int $maxDevices): string
    {
        return $this->client->request('POST', '/api/equipment_cabinets', ['json' => ['maxDevices' => $maxDevices]])
            ->toArray()['@id'];
    }

    private function createDevice(?string $cabinet = null, bool $defective = false): string
    {
        $response = $this->client->request('POST', '/api/devices', ['json' => [
            'type' => 'laptop',
            'serialNumber' => uniqid('SN-'),
            'defective' => $defective,
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

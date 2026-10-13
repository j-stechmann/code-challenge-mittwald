<?php

namespace App\DataFixtures;

use App\Entity\Device;
use App\Entity\Employee;
use App\Entity\EquipmentCabinet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads the sample data from the task description, plus two employees,
 * one of whom has borrowed the laptop.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $small = (new EquipmentCabinet())->setMaxDevices(2);
        $large = (new EquipmentCabinet())->setMaxDevices(10);
        $manager->persist($small);
        $manager->persist($large);

        $erika = (new Employee())->setName('Erika Musterfrau');
        $max = (new Employee())->setName('Max Mustermann');
        $manager->persist($erika);
        $manager->persist($max);

        // A loaned device has no cabinet; it gets one again when it is returned.
        $devices = [
            ['Laptop', 'Lenovo', 'LNV-4821', false, null, $erika],
            ['Headset', 'Jabra', 'JBR-0934', false, $small, null],
            ['Monitor', 'Dell', 'DEL-7710', true, $large, null],
        ];

        foreach ($devices as [$type, $manufacturer, $serialNumber, $defective, $cabinet, $borrower]) {
            $manager->persist((new Device())
                ->setType($type)
                ->setManufacturer($manufacturer)
                ->setSerialNumber($serialNumber)
                ->setDefective($defective)
                ->setCabinet($cabinet)
                ->setBorrower($borrower));
        }

        $manager->flush();
    }
}

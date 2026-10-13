<?php

namespace App\DataFixtures;

use App\Entity\Device;
use App\Entity\EquipmentCabinet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads the sample data from the task description.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $small = (new EquipmentCabinet())->setMaxDevices(2);
        $large = (new EquipmentCabinet())->setMaxDevices(10);
        $manager->persist($small);
        $manager->persist($large);

        $devices = [
            ['Laptop', 'Lenovo', 'LNV-4821', false, $small],
            ['Headset', 'Jabra', 'JBR-0934', false, $small],
            ['Monitor', 'Dell', 'DEL-7710', true, $large],
        ];

        foreach ($devices as [$type, $manufacturer, $serialNumber, $defective, $cabinet]) {
            $manager->persist((new Device())
                ->setType($type)
                ->setManufacturer($manufacturer)
                ->setSerialNumber($serialNumber)
                ->setDefective($defective)
                ->setCabinet($cabinet));
        }

        $manager->flush();
    }
}

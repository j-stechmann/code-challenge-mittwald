<?php

namespace App\Entity;

use App\Repository\EquipmentCabinetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentCabinetRepository::class)]
class EquipmentCabinet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxDevices = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxDevices(): ?int
    {
        return $this->maxDevices;
    }

    public function setMaxDevices(int $maxDevices): static
    {
        $this->maxDevices = $maxDevices;

        return $this;
    }
}

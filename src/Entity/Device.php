<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\DeviceRepository;
use App\State\DeviceProcessor;
use App\Validator\CabinetHasCapacity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(processor: DeviceProcessor::class),
    new Patch(processor: DeviceProcessor::class),
    new Delete(),
])]
#[CabinetHasCapacity]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $serialNumber = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $defective = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: true)]
    private ?EquipmentCabinet $cabinet = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $manufacturer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function isDefective(): ?bool
    {
        return $this->defective;
    }

    public function setDefective(bool $defective): static
    {
        $this->defective = $defective;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getCabinet(): ?EquipmentCabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?EquipmentCabinet $cabinet): static
    {
        $this->cabinet = $cabinet;

        return $this;
    }
}

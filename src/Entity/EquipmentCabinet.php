<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\EquipmentCabinetRepository;
use App\State\CabinetProcessor;
use App\Validator\CabinetIsEmpty;
use App\Validator\MaxDevicesNotBelowAssigned;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipmentCabinetRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Patch(processor: CabinetProcessor::class),
    new Delete(validate: true, validationContext: ['groups' => ['delete']]),
])]
#[MaxDevicesNotBelowAssigned]
#[CabinetIsEmpty(groups: ['delete'])]
class EquipmentCabinet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?int $maxDevices = null;

    /**
     * Devices are assigned through Device::setCabinet() (the owning side).
     *
     * @var Collection<int, Device>
     */
    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'cabinet')]
    #[ApiProperty(writable: false)]
    private Collection $devices;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Device>
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }
}

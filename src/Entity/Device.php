<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Repository\DeviceRepository;
use App\State\DeviceProcessor;
use App\Validator\CabinetHasCapacity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(operations: [
    new Get(),
    new GetCollection(parameters: [
        'type' => new QueryParameter(filter: new PartialSearchFilter(), property: 'type'),
        'manufacturer' => new QueryParameter(filter: new PartialSearchFilter(), property: 'manufacturer'),
        'serialNumber' => new QueryParameter(filter: new PartialSearchFilter(), property: 'serialNumber'),
        'defective' => new QueryParameter(filter: new ExactFilter(), property: 'defective', nativeType: new BuiltinType(TypeIdentifier::BOOL), castToNativeType: true),
    ]),
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

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Expression('value === null or this.getCabinet() === null', message: 'A device on loan can\'t be stored in a cabinet.')]
    #[Assert\Expression('value === null or !this.isDefective()', message: 'Defective devices can\'t be loaned.')]
    private ?Employee $borrower = null;

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

    public function getBorrower(): ?Employee
    {
        return $this->borrower;
    }

    public function setBorrower(?Employee $borrower): static
    {
        $this->borrower = $borrower;

        return $this;
    }
}

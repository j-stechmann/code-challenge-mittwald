<?php

namespace App\Validator;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class CabinetHasCapacityValidator extends ConstraintValidator
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CabinetHasCapacity) {
            throw new UnexpectedTypeException($constraint, CabinetHasCapacity::class);
        }
        if (!$value instanceof Device) {
            throw new UnexpectedValueException($value, Device::class);
        }

        $cabinet = $value->getCabinet();
        if (null === $cabinet?->getId() || null === $cabinet->getMaxDevices()) {
            return;
        }

        if ($this->devices->countByCabinet($cabinet, $value) >= $cabinet->getMaxDevices()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ max }}', (string) $cabinet->getMaxDevices())
                ->atPath('cabinet')
                ->addViolation();
        }
    }
}

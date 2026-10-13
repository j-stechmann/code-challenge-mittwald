<?php

namespace App\Validator;

use App\Entity\EquipmentCabinet;
use App\Repository\DeviceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class MaxDevicesNotBelowAssignedValidator extends ConstraintValidator
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxDevicesNotBelowAssigned) {
            throw new UnexpectedTypeException($constraint, MaxDevicesNotBelowAssigned::class);
        }
        if (!$value instanceof EquipmentCabinet) {
            throw new UnexpectedValueException($value, EquipmentCabinet::class);
        }

        // A cabinet that isn't persisted yet can't have devices assigned.
        if (null === $value->getId() || null === $value->getMaxDevices()) {
            return;
        }

        $count = $this->devices->countByCabinet($value);
        if ($count > $value->getMaxDevices()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ count }}', (string) $count)
                ->atPath('maxDevices')
                ->addViolation();
        }
    }
}

<?php

namespace App\Validator;

use App\Entity\EquipmentCabinet;
use App\Repository\DeviceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class CabinetIsEmptyValidator extends ConstraintValidator
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CabinetIsEmpty) {
            throw new UnexpectedTypeException($constraint, CabinetIsEmpty::class);
        }
        if (!$value instanceof EquipmentCabinet) {
            throw new UnexpectedValueException($value, EquipmentCabinet::class);
        }

        if (null === $value->getId()) {
            return;
        }

        $count = $this->devices->countByCabinet($value);
        if ($count > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ count }}', (string) $count)
                ->addViolation();
        }
    }
}

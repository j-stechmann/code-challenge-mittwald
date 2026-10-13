<?php

namespace App\Validator;

use App\Entity\Employee;
use App\Repository\DeviceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class EmployeeHasNoLoansValidator extends ConstraintValidator
{
    public function __construct(private DeviceRepository $devices)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmployeeHasNoLoans) {
            throw new UnexpectedTypeException($constraint, EmployeeHasNoLoans::class);
        }
        if (!$value instanceof Employee) {
            throw new UnexpectedValueException($value, Employee::class);
        }

        if (null === $value->getId()) {
            return;
        }

        $count = $this->devices->countByBorrower($value);
        if ($count > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ count }}', (string) $count)
                ->addViolation();
        }
    }
}

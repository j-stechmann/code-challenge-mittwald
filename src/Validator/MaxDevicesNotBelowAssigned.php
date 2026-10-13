<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class MaxDevicesNotBelowAssigned extends Constraint
{
    public string $message = 'maxDevices cannot be lower than the {{ count }} devices currently assigned to this cabinet.';

    /**
     * @param string[]|null $groups
     */
    public function __construct(?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

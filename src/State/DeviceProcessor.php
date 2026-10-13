<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Device;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Persists a Device while holding a row lock on its target cabinet, so that
 * concurrent assignments can't push the cabinet past its maxDevices.
 *
 * @implements ProcessorInterface<Device, Device>
 */
final class DeviceProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Device
    {
        $cabinet = $data->getCabinet();
        // Leaving a cabinet (or never joining one) can't exceed any capacity.
        if (null === $cabinet) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->entityManager->wrapInTransaction(function () use ($data, $cabinet, $operation, $uriVariables, $context): Device {
            $this->entityManager->lock($cabinet, LockMode::PESSIMISTIC_WRITE);

            // Re-run validation now that no one else can change the cabinet's devices.
            $violations = $this->validator->validate($data);
            if (\count($violations) > 0) {
                throw new ValidationException($violations);
            }

            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        });
    }
}

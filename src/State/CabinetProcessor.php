<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\EquipmentCabinet;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Persists changes to an existing cabinet while holding a row lock on it, so
 * that lowering maxDevices can't race with a device being assigned to it.
 *
 * @implements ProcessorInterface<EquipmentCabinet, EquipmentCabinet>
 */
final class CabinetProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): EquipmentCabinet
    {
        return $this->entityManager->wrapInTransaction(function () use ($data, $operation, $uriVariables, $context): EquipmentCabinet {
            $this->entityManager->lock($data, LockMode::PESSIMISTIC_WRITE);

            // Re-run validation now that no device can be assigned concurrently.
            $violations = $this->validator->validate($data);
            if (\count($violations) > 0) {
                throw new ValidationException($violations);
            }

            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        });
    }
}

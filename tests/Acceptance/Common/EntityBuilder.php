<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Common;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Build an entity with the given array of data. These data must match the entity's standard format.
 */
final class EntityBuilder
{
    /** @var SimpleFactoryInterface */
    private $resourceFactory;

    /** @var ObjectUpdaterInterface */
    private $resourceUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param SimpleFactoryInterface $resourceFactory
     * @param ObjectUpdaterInterface $resourceUpdate
     * @param ValidatorInterface     $validator
     */
    public function __construct(
        SimpleFactoryInterface $resourceFactory,
        ObjectUpdaterInterface $resourceUpdate,
        ValidatorInterface $validator
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceUpdater = $resourceUpdate;
        $this->validator = $validator;
    }

    /**
     * Build an entity
     *
     * @param array $data
     *
     * @return object
     *
     * @throws \Exception
     */
    public function build(array $data)
    {
        $entity = $this->resourceFactory->create();
        $this->resourceUpdater->update($entity, $data);
        $errors = $this->validator->validate($entity);

        if (0 !== $errors->count()) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = sprintf(
                    "\n- property path: %s\n- message: %s",
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }

            throw new \Exception("An error occurred on resource creation:" . implode("\n", $errorMessages));
        }

        return $entity;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Common;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
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
     * @throws \InvalidArgumentException
     */
    public function build(array $data, bool $withValidation = false): object
    {
        $entity = $this->resourceFactory->create();
        $this->resourceUpdater->update($entity, $data);

        // @todo remove condition and always perfom validation
        // Several validation constraints are couple to doctrine. That's means it is impossible to use this builder
        // for creating object for acceptance tests. For instance, UniqueEntity constraint will always use the
        // doctrine repository instead of the in memory one.
        if (true === $withValidation) {
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

                throw new \InvalidArgumentException(
                    "An error occurred on resource creation:".implode("\n", $errorMessages)
                );
            }
        }

        return $entity;
    }
}

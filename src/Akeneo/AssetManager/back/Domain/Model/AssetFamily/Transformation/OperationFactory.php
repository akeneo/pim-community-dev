<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

/**
 * Factory for the operations.
 * Initialized with a list of operations, it creates an instance of Operation interface given its type and parameters.
 */
class OperationFactory
{
    private array $operationClasses = [];

    public function __construct(array $operationClasses)
    {
        foreach ($operationClasses as $operationClass) {
            $this->addOperationClass($operationClass);
        }
    }

    public function create(string $operationType, array $parameters): Operation
    {
        if (!array_key_exists($operationType, $this->operationClasses)) {
            throw new UnknownOperationException(sprintf('Operation "%s" is unknown.', $operationType));
        }

        $class = $this->operationClasses[$operationType];

        return $class::create($parameters);
    }

    private function addOperationClass(string $class): void
    {
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf("Class '%s' does not exist.", $class));
        }

        if (!is_subclass_of($class, Operation::class)) {
            throw new \RuntimeException(sprintf("Class '%s' does not implement Operation interface.", $class));
        }

        $this->operationClasses[$class::getType()] = $class;
    }
}

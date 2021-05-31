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

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;

final class OperationApplierRegistry
{
    /** @var OperationApplier[] */
    private array $operationAppliers = [];

    public function __construct(iterable $operationAppliers)
    {
        foreach ($operationAppliers as $operationApplier) {
            $this->add($operationApplier);
        }
    }

    public function getApplier(Operation $operation): OperationApplier
    {
        foreach ($this->operationAppliers as $operationApplier) {
            if ($operationApplier->supports($operation)) {
                return $operationApplier;
            }
        }

        throw new TransformationException(
            sprintf('No applier was registered to handle operation %s', $operation::getType())
        );
    }

    private function add(OperationApplier $operationApplier): void
    {
        $this->operationAppliers[] = $operationApplier;
    }
}

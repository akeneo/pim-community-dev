<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SourceParameterApplier
{
    /**
     * @param SourceParameterApplierInterface[] $sourceParameterAppliers
     */
    public function __construct(
        private iterable $sourceParameterAppliers,
    ) {
    }

    public function apply(SourceParameterInterface $sourceParameter, string $value): string
    {
        foreach ($this->sourceParameterAppliers as $sourceParameterApplier) {
            if ($sourceParameterApplier->supports($sourceParameter, $value)) {
                return $sourceParameterApplier->applySourceParameter($sourceParameter, $value);
            }
        }

        return $value;
    }
}

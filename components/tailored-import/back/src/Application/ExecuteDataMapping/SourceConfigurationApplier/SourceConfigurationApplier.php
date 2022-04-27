<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SourceConfigurationApplier
{
    /**
     * @param SourceConfigurationApplierInterface[] $sourceConfigurationAppliers
     */
    public function __construct(
        private iterable $sourceConfigurationAppliers,
    ) {
    }

    public function apply(SourceConfigurationInterface $sourceConfiguration, string $value): string
    {
        foreach ($this->sourceConfigurationAppliers as $sourceConfigurationApplier) {
            if ($sourceConfigurationApplier->supports($sourceConfiguration, $value)) {
                return $sourceConfigurationApplier->applySourceConfiguration($sourceConfiguration, $value);
            }
        }

        return $value;
    }
}

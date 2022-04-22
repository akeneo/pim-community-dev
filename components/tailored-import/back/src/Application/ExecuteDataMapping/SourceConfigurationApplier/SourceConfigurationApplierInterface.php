<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceConfigurationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SourceConfigurationApplierInterface
{
    public function applySourceConfiguration(SourceConfigurationInterface $sourceConfiguration, string $value): string;

    public function supports(SourceConfigurationInterface $sourceConfiguration, string $value): bool;
}

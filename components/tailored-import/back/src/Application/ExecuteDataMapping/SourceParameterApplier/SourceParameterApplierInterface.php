<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\SourceParameterApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SourceParameterApplierInterface
{
    public function applySourceParameter(SourceParameterInterface $sourceParameter, string $value): string;
    public function supports(SourceParameterInterface $sourceParameter, string $value): bool;
}

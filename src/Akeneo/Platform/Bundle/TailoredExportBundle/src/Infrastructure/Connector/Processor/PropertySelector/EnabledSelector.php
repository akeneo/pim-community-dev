<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\EnabledValue;

class EnabledSelector implements PropertySelectorInterface
{
    public function applySelection(array $selectionConfiguration, SourceValueInterface $sourceValue): string
    {
        if (!$sourceValue instanceof EnabledValue) {
            throw new \LogicException('Cannot apply Enabled selection on this entity');
        }

        return $sourceValue->getData() ? '1' : '0';
    }

    public function supports(array $selectionConfiguration, SourceValueInterface $sourceValue): bool
    {
        return $sourceValue instanceof EnabledValue;
    }
}

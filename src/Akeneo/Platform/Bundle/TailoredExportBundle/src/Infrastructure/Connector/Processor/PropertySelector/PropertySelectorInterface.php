<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

interface PropertySelectorInterface
{
    public function applySelection(array $selectionConfiguration, SourceValue $sourceValue): string;

    public function supports(array $selectionConfiguration, SourceValue $sourceValue): bool;
}

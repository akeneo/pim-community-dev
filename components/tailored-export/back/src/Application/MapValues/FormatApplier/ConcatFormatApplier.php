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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\FormatApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\TailoredExport\Application\Common\Format\ElementCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Format\SourceElement;
use Akeneo\Platform\TailoredExport\Application\Common\Format\TextElement;

class ConcatFormatApplier
{
    public function applyFormat(
        ConcatFormat $format,
        array $mappedValues,
    ): string {
        return implode(
            $format->hasSpaceBetween() ? ' ' : '',
            $this->getFormattedValues($format->getElementCollection(), $mappedValues),
        );
    }

    /**
     * @return string[]
     *
     * @throws \InvalidArgumentException
     */
    private function getFormattedValues(
        ElementCollection $elementCollection,
        array $mappedValues,
    ): array {
        $formattedValues = [];

        foreach ($elementCollection as $element) {
            if ($element instanceof TextElement) {
                $formattedValues[] = $element->getValue();
            } elseif ($element instanceof SourceElement) {
                $formattedValues[] = $mappedValues[$element->getValue()];
            } else {
                throw new \InvalidArgumentException('Unsupported element type');
            }
        }

        return $formattedValues;
    }
}

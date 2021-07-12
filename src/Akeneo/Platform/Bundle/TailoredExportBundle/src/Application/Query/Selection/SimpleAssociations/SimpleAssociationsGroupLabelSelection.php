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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleAssociations;

final class SimpleAssociationsGroupLabelSelection implements SimpleAssociationsSelectionInterface
{
    public const TYPE = 'label';

    private string $locale;
    private string $separator;

    public function __construct(
        string $locale,
        string $separator
    ) {
        $this->locale = $locale;
        $this->separator = $separator;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }
}

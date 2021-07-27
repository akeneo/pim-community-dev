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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Categories;

final class CategoriesCodeSelection implements CategoriesSelectionInterface
{
    public const TYPE = 'code';

    private string $separator;

    public function __construct(string $separator)
    {
        $this->separator = $separator;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getAllLocaleCodes(): array
    {
        return [];
    }

    public function getAllAttributeCodes(): array
    {
        return [];
    }
}

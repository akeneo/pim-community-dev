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

namespace Akeneo\Platform\TailoredExport\Domain\Query\Attribute;

interface FindViewableAttributesInterface
{
    /**
     * Returns the attributes for the given attribute types.
     * The locale code is used to find the labels.
     */
    public function execute(
        string $localeCode,
        int $limit,
        array $attributeTypes = null,
        int $offset = 0,
        string $search = null
    ): ViewableAttributesResult;
}

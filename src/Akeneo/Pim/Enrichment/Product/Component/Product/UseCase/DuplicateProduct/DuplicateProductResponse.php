<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

class DuplicateProductResponse
{
    /** @var array */
    private $uniqueAttributeValues;

    public function __construct(array $uniqueAttributeValues)
    {
        $this->uniqueAttributeValues = $uniqueAttributeValues;
    }

    public function uniqueAttributeValues(): array
    {
        return $this->uniqueAttributeValues;
    }
}

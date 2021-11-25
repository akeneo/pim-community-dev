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

class ViewableAttributesResult
{
    private int $offset;

    /** @var Attribute[] */
    private array $attributes;

    /**
     * @param Attribute[] $attributes
     */
    public function __construct(int $offset, array $attributes)
    {
        $this->offset = $offset;
        $this->attributes = $attributes;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}

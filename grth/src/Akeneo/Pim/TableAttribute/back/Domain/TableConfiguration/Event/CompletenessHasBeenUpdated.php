<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event;

final class CompletenessHasBeenUpdated
{
    public function __construct(private string $attributeCode)
    {
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}

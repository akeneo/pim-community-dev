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

namespace Akeneo\Pim\TableAttribute\Infrastructure\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class TableType extends AbstractAttributeType
{
    public function getName(): string
    {
        return AttributeTypes::TABLE;
    }
}

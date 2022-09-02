<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\TargetConstraint;

abstract class AttributeTargetConstraint extends TargetConstraint
{
    public function __construct(
        array $columnsUuids,
        private Attribute $attribute,
    ) {
        parent::__construct($columnsUuids);
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}

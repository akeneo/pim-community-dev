<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;

class EditableAttributeFilter extends \FilterIterator
{
    public function __construct(array $attributes)
    {
        parent::__construct(new \ArrayIterator($attributes));
    }

    public function accept(): bool
    {
        $attribute = $this->getInnerIterator()->current();

        if (empty($attribute['properties'])) {
            return true;
        }

        $properties = unserialize($attribute['properties']);
        $isReadable = isset($properties['is_read_only']) && $properties['is_read_only'] === true;

        return !$isReadable;
    }
}

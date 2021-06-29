<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditableAttributeFilter extends \FilterIterator
{
    public function __construct(array $attributes)
    {
        parent::__construct(new \ArrayIterator($attributes));
    }

    public function accept()
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

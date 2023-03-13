<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

use Akeneo\Category\Domain\Model\Attribute\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeAddedEvent
{
    public function __construct(private readonly Attribute $attribute)
    {
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;

class AttributeOptionWordIgnoredEvent
{
    /** @var AttributeOptionCode */
    private $attributeOptionCode;

    public function __construct(AttributeOptionCode $attributeOptionCode)
    {
        $this->attributeOptionCode = $attributeOptionCode;
    }

    public function getAttributeOptionCode(): AttributeOptionCode
    {
        return $this->attributeOptionCode;
    }
}

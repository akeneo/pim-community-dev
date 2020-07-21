<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Events;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

class AttributeWordIgnoredEvent
{
    /** @var AttributeCode */
    private $attributeCode;

    public function __construct(AttributeCode $attributeCode)
    {
        $this->attributeCode = $attributeCode;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }
}

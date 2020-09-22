<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupActivation
{
    /** @var AttributeGroupCode */
    private $attributeGroupCode;

    /** @var bool */
    private $activated;

    public function __construct(AttributeGroupCode $attributeGroupCode, bool $activated)
    {
        $this->attributeGroupCode = $attributeGroupCode;
        $this->activated = $activated;
    }

    public function getAttributeGroupCode(): AttributeGroupCode
    {
        return $this->attributeGroupCode;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }
}

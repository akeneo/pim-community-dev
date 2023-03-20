<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupsActivation
{
    public function __construct(
        private readonly array $attributeGroupsActivation,
    ) {
        Assert::allBoolean(array_values($attributeGroupsActivation));
    }

    public function isActivated(string $attributeCode): bool
    {
        return $this->attributeGroupsActivation[$attributeCode] ?? false;
    }
}

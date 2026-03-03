<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OnlySaasSandboxFeatureFlag implements FeatureFlag
{
    private const SAAS_SANDBOX_PRODUCT_CODES = [
        'serenity_sandbox',
        'growth_edition_sandbox',
    ];

    public function __construct(private readonly string $productReferenceCode)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return \in_array($this->productReferenceCode, self::SAAS_SANDBOX_PRODUCT_CODES);
    }
}

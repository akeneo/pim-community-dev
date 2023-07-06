<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class AppDeveloperModeFeature implements FeatureFlag
{
    private const PRODUCT_REFERENCE_CODES = [
        'serenity_dev',
        'serenity_sandbox',
        'growth_edition_dev',
    ];

    public function __construct(private readonly string $productReferenceCode)
    {
    }

    public function isEnabled(?string $feature = null): bool
    {
        return in_array($this->productReferenceCode, self::PRODUCT_REFERENCE_CODES);
    }
}

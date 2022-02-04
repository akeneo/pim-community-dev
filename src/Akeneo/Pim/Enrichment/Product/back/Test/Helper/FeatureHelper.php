<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Helper;

use PhpSpec\Exception\Example\SkippingException;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FeatureHelper
{
    /**
     * @throws SkippingException
     */
    public static function skipSpecTestWhenPermissionFeatureIsNotActivated(): void
    {
        if (!\class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle')) {
            throw new SkippingException('Permission feature is not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenPermissionFeatureIsNotActivated(): void
    {
        if (!\class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle')) {
            Assert::markTestSkipped('Permission feature is not available in this scope');
        }
    }
}

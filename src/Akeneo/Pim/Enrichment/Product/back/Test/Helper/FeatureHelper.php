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
        if (!self::isPermissionFeatureActivated()) {
            throw new SkippingException('Permission feature is not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenPermissionFeatureIsNotActivated(): void
    {
        if (!self::isPermissionFeatureActivated()) {
            Assert::markTestSkipped('Permission feature is not available in this scope');
        }
    }

    public static function isPermissionFeatureActivated(): bool
    {
        return \class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle');
    }

    public static function skipIntegrationTestWhenAssetFeatureIsNotActivated(): void
    {
        $isAssetFeatureActivated = \class_exists('Akeneo\Pim\Enrichment\AssetManager\Bundle\AkeneoPimEnrichmentAssetManagerBundle');
        if (!$isAssetFeatureActivated) {
            Assert::markTestSkipped('Asset feature is not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenReferenceEntityIsNotActivated(): void
    {
        $isAssetFeatureActivated = \class_exists('Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle');
        if (!$isAssetFeatureActivated) {
            Assert::markTestSkipped('Asset feature is not available in this scope');
        }
    }
}

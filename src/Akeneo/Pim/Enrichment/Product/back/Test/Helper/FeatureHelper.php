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
        if (!self::isPermissionFeatureAvailable()) {
            throw new SkippingException('Permission feature is not available in this scope');
        }
    }

    /**
     * @throws SkippingException
     */
    public static function skipSpecTestWhenReferenceEntityFeatureIsNotActivated(): void
    {
        if (!self::isReferenceEntityFeatureActivated()) {
            throw new SkippingException('Reference entity feature is not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenPermissionFeatureIsNotAvailable(): void
    {
        if (!self::isPermissionFeatureAvailable()) {
            Assert::markTestSkipped('Permission feature is not available in this scope');
        }
    }

    public static function isPermissionFeatureAvailable(): bool
    {
        return \class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle');
    }

    public static function isReferenceEntityFeatureActivated(): bool
    {
        return \class_exists('Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle', true);
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
        $isRefEntityFeatureActivated = \class_exists('Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle');
        if (!$isRefEntityFeatureActivated) {
            Assert::markTestSkipped('Reference Entity feature is not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenTableAttributeIsNotActivated(): void
    {
        $isTableAttributeFeatureActivated = \class_exists('Akeneo\Pim\TableAttribute\Infrastructure\Symfony\AkeneoPimTableAttributeBundle');
        if (!$isTableAttributeFeatureActivated) {
            Assert::markTestSkipped('Table attribute feature is not available in this scope');
        }
    }
}

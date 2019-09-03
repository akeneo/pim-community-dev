<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\IsAttributeLocalizableInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\IsAttributeScopableInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlIsAttributeLocalizableTest extends SqlIntegrationTestCase
{
    private const ASSET_FAMILY_IDENTIFIER = 'designer';
    private const ATTRIBUTE_CODE = 'name';
    private const UNKNOWN_ATTRIBUTE_CODE = 'UNKNOWN_ATTRIBUTE';

    /** @var IsAttributeLocalizableInterface */
    private $isAttributeLocalizable;

    public function setUp(): void
    {
        parent::setUp();

        $this->isAttributeLocalizable = $this->get('akeneo_assetmanager.infrastructure.persistence.query.is_attribute_localizable');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_is_localizable()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE, false, true)
            ->load();

        $isLocalizable = $this->isAttributeLocalizable->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::ATTRIBUTE_CODE)
        );

        Assert::assertTrue($isLocalizable);
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_attribute_is_not_localizable()
    {
        $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_CODE, false, false)
            ->load();

        $isLocalizable = $this->isAttributeLocalizable->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::ATTRIBUTE_CODE)
        );

        Assert::assertFalse($isLocalizable);
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_does_not_exist()
    {
        $this->expectException(AttributeNotFoundException::class);
        $this->isAttributeLocalizable->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::UNKNOWN_ATTRIBUTE_CODE)
        );
    }
}

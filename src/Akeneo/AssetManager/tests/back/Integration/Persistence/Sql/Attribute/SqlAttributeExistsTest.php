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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlAttributeExistsTest extends SqlIntegrationTestCase
{
    private AttributeExistsInterface $attributeExists;

    private ?array $fixtures = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.attribute_exists');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $identifier = $this->fixtures['attributes']['name']->getIdentifier();
        $isExisting = $this->attributeExists->withIdentifier($identifier);
        Assert::assertTrue($isExisting);
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_attribute_does_not_exist_for_the_given_identifier()
    {
        $isExisting = $this->attributeExists->withIdentifier(AttributeIdentifier::create('designer', 'name', 'none'));
        Assert::assertFalse($isExisting);
    }

    /**
     * @test
     */
    public function it_says_if_the_attribute_exists_for_the_given_asset_family_identifier_and_order()
    {
        $isExistingAtOrder2 = $this->attributeExists->withAssetFamilyIdentifierAndOrder(AssetFamilyIdentifier::fromString('designer'), AttributeOrder::fromInteger(2));
        $isExistingAtOrder3 = $this->attributeExists->withAssetFamilyIdentifierAndOrder(AssetFamilyIdentifier::fromString('designer'), AttributeOrder::fromInteger(3));

        Assert::assertTrue($isExistingAtOrder2);
        Assert::assertFalse($isExistingAtOrder3);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['name'])
            ->load();
    }
}

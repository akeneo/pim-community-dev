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
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlGetAttributeTypeTest extends SqlIntegrationTestCase
{
    private const ASSET_FAMILY_IDENTIFIER = 'designer';
    private const EXISTING_TEXT_ATTRIBUTE_CODE = 'name';
    private const UNKNOWN_ATTRIBUTE_CODE = 'UNKNOWN_ATTRIBUTE';

    private GetAttributeTypeInterface $getAttributeType;

    private ?array $fixtures = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->getAttributeType = $this->get('akeneo_assetmanager.infrastructure.persistence.query.get_attribute_type');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_attribute_type_of_an_existing_attribute()
    {
        $expectedAttributeType = 'text';

        $actualAttributeType = $this->getAttributeType->fetch(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::EXISTING_TEXT_ATTRIBUTE_CODE)
        );

        Assert::assertEquals($expectedAttributeType, $actualAttributeType);
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_does_not_exists()
    {
        $this->expectException(AttributeNotFoundException::class);
        $this->getAttributeType->fetch(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::UNKNOWN_ATTRIBUTE_CODE)
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixtures = $this->fixturesLoader
            ->assetFamily(self::ASSET_FAMILY_IDENTIFIER)
            ->withAttributeOfTypeText(self::ASSET_FAMILY_IDENTIFIER, self::EXISTING_TEXT_ATTRIBUTE_CODE)
            ->load();
    }
}

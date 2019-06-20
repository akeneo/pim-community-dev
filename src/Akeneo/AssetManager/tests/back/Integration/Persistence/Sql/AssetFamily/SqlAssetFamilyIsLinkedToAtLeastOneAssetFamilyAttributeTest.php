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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeTest extends SqlIntegrationTestCase
{
    /** @var AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.asset_family_is_linked_to_at_least_one_asset_family_attribute');
        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_tells_if_an_asset_family_is_linked_to_at_least_one_asset_family_attribute()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertTrue($isLinked);

        $identifier = AssetFamilyIdentifier::fromString('brand');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertTrue($isLinked);

        $identifier = AssetFamilyIdentifier::fromString('color');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertFalse($isLinked);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $designer = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'],
            Image::createEmpty()
        );
        $brand = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            ['fr_FR' => 'Marque', 'en_US' => 'Brand'],
            Image::createEmpty()
        );
        $color = AssetFamily::create(
            AssetFamilyIdentifier::fromString('color'),
            ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
            Image::createEmpty()
        );
        $assetFamilyRepository->create($designer);
        $assetFamilyRepository->create($brand);
        $assetFamilyRepository->create($color);

        $mentor = AssetAttribute::create(
            AttributeIdentifier::fromString('mentor_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString('designer')
        );
        $brands = AssetAttribute::create(
            AttributeIdentifier::fromString('brands_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('brands'),
            LabelCollection::fromArray(['en_US' => 'Brands']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString('brand')
        );
        $attributeRepository->create($mentor);
        $attributeRepository->create($brands);
    }
}

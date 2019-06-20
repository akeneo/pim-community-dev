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

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetFamilyAttributeAsImageTest extends SqlIntegrationTestCase
{
    /** @var FindAssetFamilyAttributeAsLabelInterface */
    private $findAttributeAsImage;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributeAsImage = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_attribute_as_image');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_image_of_an_asset_family()
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $expectedAttributeAsImage = $assetFamily->getAttributeAsImageReference();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeAsImage = $this->findAttributeAsImage->find($assetFamilyIdentifier);

        $this->assertEquals($expectedAttributeAsImage, $attributeAsImage);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_image_if_the_asset_family_was_not_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown');
        $attributeAsImage = $this->findAttributeAsImage->find($assetFamilyIdentifier);

        $this->assertTrue($attributeAsImage->isEmpty());
    }

    private function loadFixtures(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}

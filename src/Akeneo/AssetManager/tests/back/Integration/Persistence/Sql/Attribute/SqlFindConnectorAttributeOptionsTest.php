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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindConnectorAttributeOptionsTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private FindConnectorAttributeOptionsInterface $findConnectorAttributeOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->findConnectorAttributeOption = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_attribute_options');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_options_for_an_attribute()
    {
        $assetFamilyIdentifier = 'asset_family';
        $this->createAssetFamily($assetFamilyIdentifier);
        $this->createAttribute($assetFamilyIdentifier);

        $foundAttributeOptions = $this->findConnectorAttributeOption->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('attribute_1_code')
        );

        $normalizedFoundOptions = [];

        foreach ($foundAttributeOptions as $option) {
            $normalizedFoundOptions[] = $option->normalize();
        }

        $this->assertSame(
            [
                AttributeOption::create(
                    OptionCode::fromString('french'),
                    LabelCollection::fromArray(['fr_FR' => 'Francais'])
                )->normalize(),
                AttributeOption::create(
                    OptionCode::fromString('english'),
                    LabelCollection::fromArray(['fr_FR' => 'Angalis'])
                )->normalize()
            ],
            $normalizedFoundOptions
        );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_attribute_has_no_options()
    {
        $assetFamilyIdentifier = 'asset_family_test';
        $this->createAssetFamily($assetFamilyIdentifier);
        $this->createAttributeWithNoOptions($assetFamilyIdentifier);

        $foundOptions = $this->findConnectorAttributeOption->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('no_options')
        );

        $this->assertSame([], $foundOptions);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createMediaFileAttribute(string $assetFamilyIdentifier)
    {
        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, 'portrait', 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($mediaFileAttribute);
    }

    private function createAttribute(string $assetFamilyIdentifier)
    {
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, 'attribute_1', 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('attribute_1_code'),
            LabelCollection::fromArray(['en_US' => 'Attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $optionCollectionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais', 'es_ES' => 'Francés'])
            ),
            AttributeOption::create(
                OptionCode::fromString('english'),
                LabelCollection::fromArray(['fr_FR' => 'Angalis', 'es_ES' => 'Inglés'])
            )
        ]);

        $this->attributeRepository->create($optionCollectionAttribute);
    }

    private function createAttributeWithNoOptions(string $assetFamilyIdentifier)
    {
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, 'no_options', 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('no_options'),
            LabelCollection::fromArray(['en_US' => 'Attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $optionCollectionAttribute->setOptions([]);

        $this->attributeRepository->create($optionCollectionAttribute);
    }

    private function createAssetFamily(string $rawIdentifier): AssetFamily
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($rawIdentifier);

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => $rawIdentifier],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }
}

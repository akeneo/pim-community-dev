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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindConnectorAttributeOptionTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private FindConnectorAttributeOptionInterface $findConnectorAttributeOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->findConnectorAttributeOption = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_attribute_option');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_an_option_for_an_attribute()
    {
        $assetFamilyIdentifier = 'asset_family';
        $this->createAssetFamily($assetFamilyIdentifier);
        $this->createConnectorAttribute($assetFamilyIdentifier);

        $foundAttributeOption = $this->findConnectorAttributeOption->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('attribute_1_code'),
            OptionCode::fromString('french')
        );

        $this->assertSame(
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais'])
            )->normalize(),
            $foundAttributeOption->normalize()
        );
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_option_found()
    {
        $foundAttribute = $this->findConnectorAttributeOption->find(
            AssetFamilyIdentifier::fromString('asset_family'),
            AttributeCode::fromString('none'),
            OptionCode::fromString('whatever')
        );

        $this->assertSame(null, $foundAttribute);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttribute(string $assetFamilyIdentifier)
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

        return new ConnectorAttribute(
            $optionCollectionAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            'media_file',
            AttributeValuePerLocale::fromBoolean($optionCollectionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionCollectionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'options' => array_map(
                    fn(AttributeOption $attributeOption) => $attributeOption->normalize(),
                    $optionCollectionAttribute->getAttributeOptions()
                ),
            ]
        );
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

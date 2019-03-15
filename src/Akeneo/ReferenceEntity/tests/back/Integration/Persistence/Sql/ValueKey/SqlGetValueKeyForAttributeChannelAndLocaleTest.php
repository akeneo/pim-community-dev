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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ValueKey\SqlGetValueKeyForAttributeChannelAndLocale;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlGetValueKeyForAttributeChannelAndLocaleTest extends SqlIntegrationTestCase
{
    /** @var SqlGetValueKeyForAttributeChannelAndLocale */
    private $getValueKeyForAttributeChannelAndLocale;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->getValueKeyForAttributeChannelAndLocale = $this->get('akeneo_referenceentity.infrastructure.persistence.query.get_value_key_for_attribute_channel_and_locale');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadReferenceEntities();
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_without_channel_nor_locale()
    {
        $this->loadAttributeWithoutValuePerChannelOrLocale('attribute_without_channel_nor_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_without_channel_nor_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_without_channel_nor_locale', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_channel()
    {
        $this->loadAttributeWithValuePerChannel('attribute_with_value_per_channel');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_channel'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_channel_ecommerce', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_locale()
    {
        $this->loadAttributeWithValuePerLocale('attribute_with_value_per_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_locale_fr_FR', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_channel_and_value_per_locale()
    {
        $this->loadAttributeWithValuePerChannelAndLocale('attribute_with_value_per_channel_and_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_channel_and_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_channel_and_locale_ecommerce_fr_FR', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_throws_if_the_value_key_is_not_found()
    {
        $this->expectException(\LogicException::class);
        $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('unknown_attribute'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntities(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $designer = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($designer);
    }

    private function loadAttributeWithoutValuePerChannelOrLocale(string $attributeIdentifier): void
    {
        $attribute = RecordAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerChannel(string $attributeIdentifier): void
    {
        $attribute = ImageAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerLocale(string $attributeIdentifier): void
    {
        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerChannelAndLocale(string $attributeIdentifier): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::fromString($attributeIdentifier),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }
}

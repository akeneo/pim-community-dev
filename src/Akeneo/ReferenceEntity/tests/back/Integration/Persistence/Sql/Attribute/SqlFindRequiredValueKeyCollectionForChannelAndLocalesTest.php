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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
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
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * ----------------------------------
 * |  Channel  |  Activated locales |
 * |-----------|--------------------|
 * | ecommerce | fr_FR, en_US       |
 * | mobile    | de_DE              |
 * ----------------------------------
 */
class SqlFindRequiredValueKeyCollectionForChannelAndLocalesTest extends SqlIntegrationTestCase
{
    /** @var FindRequiredValueKeyCollectionForChannelAndLocalesInterface */
    private $findRequiredValueKeyCollection;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    private $order = 0;

    public function setUp()
    {
        parent::setUp();

        $this->findRequiredValueKeyCollection = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locales');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadReferenceEntities();
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_collection_of_required_attributes_of_a_reference_entity()
    {
        $designer = ReferenceEntityIdentifier::fromString('designer');
        $channel = ChannelIdentifier::fromCode('ecommerce');
        $locales = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'en_US', 'en_AU']);

        $country = $this->loadRequiredAttributeWithoutValuePerChannelOrLocale('country');
        $image = $this->loadRequiredAttributeWithValuePerChannel('image');
        $name = $this->loadRequiredAttributeWithValuePerChannelAndLocale('name');
        $materials = $this->loadRequiredAttributeWithValuePerLocale('materials');
        $age = $this->loadNotRequiredAttribute('age');

        $actualValueKeyCollection = ($this->findRequiredValueKeyCollection)($designer, $channel, $locales);

        $this->assertInstanceOf(ValueKeyCollection::class, $actualValueKeyCollection);
        $normalizedActualValueKeyCollection = $actualValueKeyCollection->normalize();
        $this->assertCount(6, $normalizedActualValueKeyCollection);

        $this->assertContains(sprintf('%s', $country->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce', $image->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $name->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_fr_FR', $materials->getIdentifier()), $normalizedActualValueKeyCollection);
        $this->assertContains(sprintf('%s_en_US', $materials->getIdentifier()), $normalizedActualValueKeyCollection);

        $this->assertNotContains(sprintf('%s', $age->getIdentifier()), $normalizedActualValueKeyCollection);
    }

    /**
     * @test
     */
    public function it_does_not_return_value_keys_for_the_locales_that_are_not_activated_for_the_channel()
    {
        $this->loadRequiredAttributeWithValuePerChannelAndLocale('name');

        $designer = ReferenceEntityIdentifier::fromString('designer');
        $channel = ChannelIdentifier::fromCode('mobile');
        $locales = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'en_US']);

        $valueKeyCollection = ($this->findRequiredValueKeyCollection)($designer, $channel, $locales);
        $this->assertInstanceOf(ValueKeyCollection::class, $valueKeyCollection);
        $this->assertEmpty($valueKeyCollection->normalize());
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

        $country = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('country'),
            [
                'fr_FR' => 'Pays',
                'en_US' => 'Country',
            ],
            Image::createEmpty()
        );

        $referenceEntityRepository->create($designer);
        $referenceEntityRepository->create($country);
    }

    private function loadRequiredAttributeWithoutValuePerChannelOrLocale(string $attributeCode): AbstractAttribute
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = RecordAttribute::create(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        $this->attributeRepository->create($attribute);

        return $attribute;
    }

    private function loadRequiredAttributeWithValuePerChannel(string $attributeCode): AbstractAttribute
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = ImageAttribute::create(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $this->attributeRepository->create($attribute);

        return $attribute;
    }

    private function loadRequiredAttributeWithValuePerLocale(string $attributeCode): AbstractAttribute
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = OptionCollectionAttribute::create(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($attribute);

        return $attribute;
    }

    private function loadRequiredAttributeWithValuePerChannelAndLocale(string $attributeCode): AbstractAttribute
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);

        return $attribute;
    }

    private function loadNotRequiredAttribute(string $attributeCode): AbstractAttribute
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);

        return $attribute;
    }
}

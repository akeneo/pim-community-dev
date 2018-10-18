<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record\Query;

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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindValueKeysToIndexForChannelAndLocale;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForChannelAndLocaleTest extends SqlIntegrationTestCase
{
    /** @var SqlFindValueKeysToIndexForChannelAndLocale */
    private $findValuesToIndexForChannelAndLocale;

    public function setUp()
    {
        parent::setUp();

        $this->findValuesToIndexForChannelAndLocale = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_values_to_index_for_channel_and_locale');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_generates_an_empty_list()
    {
        $valueKeyCollection = ($this->findValuesToIndexForChannelAndLocale)(ReferenceEntityIdentifier::fromString('designer'), ChannelIdentifier::fromCode('ecommerce'), LocaleIdentifier::fromCode('en_US'));
        Assert::assertEmpty($valueKeyCollection->normalize());
    }

    /**
     * @test
     */
    public function it_generates_a_list_of_value_keys_of_text_attributes_only()
    {
        $this->loadReferenceEntityAndAttributes();
        $valueKeyCollection = ($this->findValuesToIndexForChannelAndLocale)(ReferenceEntityIdentifier::fromString('designer'),
            ChannelIdentifier::fromCode('ecommerce'), LocaleIdentifier::fromCode('en_US'));
        Assert::assertSame([
            'name_designer_fingerprint_ecommerce_en_US',
        ], $valueKeyCollection->normalize());
    }


    private function loadReferenceEntityAndAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $name = TextAttribute::createText(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($name);

        $image = ImageAttribute::create(
            AttributeIdentifier::fromString('image_designer_fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $attributeRepository->create($image);
    }
}

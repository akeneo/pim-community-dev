<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use PHPUnit\Framework\TestCase;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocaleTest extends TestCase
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocale */
    private $query;

    public function setup()
    {
        $this->attributeRepository = new InMemoryAttributeRepository();
        $this->query = new InMemoryFindRequiredValueKeyCollectionForChannelAndLocale($this->attributeRepository);
    }

    /**
     * @test
     */
    public function it_finds_required_value_key_collection_for_a_given_reference_entity_on_a_channel_and_locale()
    {
        $this->query->setActivatedChannels(['ecommerce', 'mobile']);
        $this->query->setActivatedLocales(['en_US']);

        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['en_US' => 'Name']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(155),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'nickname', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('nickname'),
                LabelCollection::fromArray(['en_US' => 'Name']),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(155),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        /** @var ValueKeyCollection $valueKeyCollection */
        $valueKeyCollection = ($this->query)(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('en_US')
        );

        $valueKeys = $valueKeyCollection->normalize();

        $this->assertInstanceOf(ValueKeyCollection::class, $valueKeyCollection);
        $this->assertContains('name_designer_fingerprint_ecommerce_en_US', $valueKeys);
        $this->assertContains('nickname_designer_fingerprint_en_US', $valueKeys);
        $this->assertNotContains('name_designer_fingerprint_mobile_en_US', $valueKeys);
    }
}

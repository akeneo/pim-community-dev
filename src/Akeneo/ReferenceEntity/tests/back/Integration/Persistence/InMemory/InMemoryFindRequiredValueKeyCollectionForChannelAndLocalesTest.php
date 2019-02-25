<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
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
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocalesTest extends TestCase
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
        $this->query = new InMemoryFindRequiredValueKeyCollectionForChannelAndLocales($this->attributeRepository);
    }

    /**
     * @test
     */
    public function it_finds_required_value_key_collection_for_a_given_reference_entity_on_a_channel_and_locale()
    {
        $this->query->setActivatedChannels(['ecommerce', 'mobile']);
        $this->query->setActivatedLocales(['en_US', 'fr_FR']);

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

        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('description'),
                LabelCollection::fromArray(['en_US' => 'Description']),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        /** @var ValueKeyCollection $valueKeyCollection */
        $valueKeyCollection = ($this->query)(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['en_US', 'fr_FR', 'de_DE'])
        );

        $valueKeys = $valueKeyCollection->normalize();

        $this->assertInstanceOf(ValueKeyCollection::class, $valueKeyCollection);
        $this->assertCount(4, $valueKeys);
        $this->assertContains('name_designer_fingerprint_ecommerce_en_US', $valueKeys);
        $this->assertContains('name_designer_fingerprint_ecommerce_fr_FR', $valueKeys);
        $this->assertContains('nickname_designer_fingerprint_en_US', $valueKeys);
        $this->assertContains('nickname_designer_fingerprint_fr_FR', $valueKeys);
        $this->assertNotContains('name_designer_fingerprint_mobile_en_US', $valueKeys);
    }
}

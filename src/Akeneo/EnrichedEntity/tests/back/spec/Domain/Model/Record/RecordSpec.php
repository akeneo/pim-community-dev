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

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = [
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ];
        $valueCollection = ValueCollection::fromValues([]);

        $this->beConstructedThrough('create', [
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            $labelCollection,
            $valueCollection
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Record::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = RecordIdentifier::fromString('designer_starck_fingerprint');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

    public function it_updates_the_value_collection()
    {
        $this->setValue(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('A description')
        ));
        $this->normalize()->shouldReturn([
            'identifier'               => 'designer_starck_fingerprint',
            'code'                     => 'starck',
            'enrichedEntityIdentifier' => 'designer',
            'labels'                   => [
                'en_US' => 'Stark',
                'fr_FR' => 'Stark',
            ],
            'values'                   => [
                'name_designer_fingerprint_ecommerce_en_US' => [
                    'attribute' => 'name_designer_fingerprint',
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'A description',
                ],
            ],
        ]);
    }

    // TODO Missing specs

    public function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $sameRecord = Record::create(
            $sameIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            [],
            ValueCollection::fromValues([])
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('designer_jony_ive_other-fingerprint');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('jony_ive'),
            [],
            ValueCollection::fromValues([])
        );
        $this->equals($anotherRecord)->shouldReturn(false);
    }
}

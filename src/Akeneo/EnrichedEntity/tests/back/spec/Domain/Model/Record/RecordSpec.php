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
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
     function let()
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
            Image::createEmpty(),
            $valueCollection
        ]);
    }

     function it_is_initializable()
    {
        $this->shouldHaveType(Record::class);
    }

     function it_returns_its_identifier()
    {
        $identifier = RecordIdentifier::fromString('designer_starck_fingerprint');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

     function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

     function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $sameRecord = Record::create(
            $sameIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('designer_jony_ive_other-fingerprint');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('jony_ive'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->equals($anotherRecord)->shouldReturn(false);
    }
    
     function it_sets_a_value_to_the_value_collection()
     {
         $valueKey = ValueKey::create(
             AttributeIdentifier::fromString('name'),
             ChannelReference::noReference(),
             LocaleReference::noReference()
         );
         $value = Value::create(
             AttributeIdentifier::fromString('name'),
             ChannelReference::noReference(),
             LocaleReference::noReference(),
             TextData::fromString('Philippe Stark')
         );
         $this->findValue($valueKey)->shouldBeNull();

         $this->setValue($value);

         $this->findValue($valueKey)->shouldBeEqualTo($value);
     }

     function it_normalizes_itself()
     {
         $this->normalize()->shouldReturn([
             'identifier' => 'designer_starck_fingerprint',
             'code' => 'starck',
             'enrichedEntityIdentifier' => 'designer',
             'labels'                   => [
                 'en_US' => 'Stark',
                 'fr_FR' => 'Stark',
             ],
             'values' => []
         ]);
     }
}

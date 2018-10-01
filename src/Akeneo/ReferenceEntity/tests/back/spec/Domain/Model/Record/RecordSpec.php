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

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
     function let()
    {
        $identifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = [
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ];
        $valueCollection = ValueCollection::fromValues([]);

        $this->beConstructedThrough('create', [
            $identifier,
            $referenceEntityIdentifier,
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

     function it_returns_the_identifier_of_the_reference_entity_it_belongs_to()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $this->getReferenceEntityIdentifier()->shouldBeLike($referenceEntityIdentifier);
    }

     function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $sameRecord = Record::create(
            $sameIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('designer_jony_ive_other-fingerprint');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
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
             'referenceEntityIdentifier' => 'designer',
             'labels'                   => [
                 'en_US' => 'Stark',
                 'fr_FR' => 'Stark',
             ],
             'values' => [],
             'image' => null,
         ]);
     }
}

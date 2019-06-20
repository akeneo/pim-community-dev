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
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
     function let()
    {
        $identifier = RecordIdentifier::fromString('designer_starck_fingerprint');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $valueCollection = ValueCollection::fromValues([
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'label', 'fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                TextData::fromString('Stark')
            ),
            Value::create(
                AttributeIdentifier::create('designer', 'description', 'fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('.one value per channel ecommerce / one value per locale fr_FR.')
            ),
        ]);

        $this->beConstructedThrough('create', [
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
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
            ValueCollection::fromValues([])
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::fromString('designer_jony_ive_other-fingerprint');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('jony_ive'),
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
             'values' => [
                 'label_designer_fingerprint_fr_FR' => [
                     'attribute' => 'label_designer_fingerprint',
                     'channel'   => null,
                     'locale'    => 'fr_FR',
                     'data'      => 'Stark',
                 ],
                 'label_designer_fingerprint_en_US' => [
                     'attribute' => 'label_designer_fingerprint',
                     'channel'   => null,
                     'locale'    => 'en_US',
                     'data'      => 'Stark',
                 ],
                 'description_designer_fingerprint_ecommerce_fr_FR' => [
                     'attribute' => 'description_designer_fingerprint',
                     'channel'   => 'ecommerce',
                     'locale'    => 'fr_FR',
                     'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                 ],
             ],
         ]);
     }

     function it_filters_values()
     {
         $this->filterValues(function(Value $value){ return false;})->normalize()->shouldReturn([]);
         $this->filterValues(function(Value $value){ return true;})
             ->findValue(ValueKey::createFromNormalized('description_designer_fingerprint_ecommerce_fr_FR'))
             ->shouldNotBeNull();
     }
}

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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
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
            Image::createEmpty(),
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

    // TODO Missing specs

    public function it_is_comparable()
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
}

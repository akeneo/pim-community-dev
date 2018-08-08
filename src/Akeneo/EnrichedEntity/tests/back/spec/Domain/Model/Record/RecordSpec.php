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
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    public function let()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = [
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ];

        $this->beConstructedThrough('create', [$identifier, $enrichedEntityIdentifier, $recordCode, $labelCollection]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Record::class);
    }

    public function it_returns_its_identifier()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');

        $this->getIdentifier()->shouldBeLike($identifier);
    }

    public function it_returns_the_identifier_of_the_enriched_entity_it_belongs_to()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->getEnrichedEntityIdentifier()->shouldBeLike($enrichedEntityIdentifier);
    }

    public function it_checks_the_identifier_and_code_are_in_sync()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [
                RecordIdentifier::create('designer', 'strack'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('wrong_code'),
                []
            ]);
    }

    public function it_checks_the_identifier_and_enriched_entity_identifier_are_in_sync()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [
                RecordIdentifier::create('designer', 'strack'),
                EnrichedEntityIdentifier::fromString('wrong_identifier'),
                RecordCode::fromString('starck'),
                []
            ]);
    }

    public function it_is_comparable()
    {
        $sameIdentifier = RecordIdentifier::create('designer', 'starck');
        $sameRecord = Record::create(
            $sameIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('starck'),
            []
        );
        $this->equals($sameRecord)->shouldReturn(true);

        $anotherIdentifier = RecordIdentifier::create('designer', 'jony_ive');
        $anotherRecord = Record::create(
            $anotherIdentifier,
            EnrichedEntityIdentifier::fromString('designer'),
            RecordCode::fromString('jony_ive'),
            []
        );
        $this->equals($anotherRecord)->shouldReturn(false);
    }
}

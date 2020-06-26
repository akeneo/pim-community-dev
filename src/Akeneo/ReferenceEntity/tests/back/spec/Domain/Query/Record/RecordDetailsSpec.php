<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use PhpSpec\ObjectBehavior;

class RecordDetailsSpec extends ObjectBehavior
{
    public function let(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->beConstructedWith(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $createdAt,
            $updatedAt,
            Image::createEmpty(),
            [],
            true
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RecordDetails::class);
    }

    public function it_normalizes_itself(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        LabelCollection $labelCollection,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {

        $identifier->normalize()->willReturn('starck_designer_fingerprint');
        $referenceEntityIdentifier->normalize()->willReturn('designer');
        $code->normalize()->willReturn('starck');
        $labelCollection->normalize()->willReturn(['fr_FR' => 'Philippe Starck']);
        $createdAt->format('c')->willReturn('2020-06-23T09:24:03-07:00');
        $updatedAt->format('c')->willReturn('2020-06-23T09:30:13-07:00');

        $this->normalize()->shouldReturn([
            'identifier'                  => 'starck_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code'                        => 'starck',
            'labels'                      => ['fr_FR' => 'Philippe Starck'],
            'created_at'                  => '2020-06-23T09:24:03-07:00',
            'updated_at'                  => '2020-06-23T09:30:13-07:00',
            'image'                       => null,
            'values'                      => [],
            'permission'                  => [
                'edit' => true,
            ],
        ]);
    }
}

<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityItem;
use PhpSpec\ObjectBehavior;

class ReferenceEntityItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityItem::class);
    }

    function it_normalizes_a_read_model(Image $image)
    {
        $image->normalize()->willReturn([
            'filePath'         => '/path/image.jpg',
            'originalFilename' => 'image.jpg'
        ]);

        $this->identifier = ReferenceEntityIdentifier::fromString('starck');
        $this->labels = LabelCollection::fromArray([
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ]);
        $this->image = $image;

        $this->normalize()->shouldReturn(
            [
                'identifier'                 => 'starck',
                'labels'                     => [
                    'fr_FR' => 'Philippe starck',
                    'en_US' => 'Philip starck',
                ],
                'image'      => [
                    'filePath'         => '/path/image.jpg',
                    'originalFilename' => 'image.jpg'
                ]
            ]
        );
    }
}

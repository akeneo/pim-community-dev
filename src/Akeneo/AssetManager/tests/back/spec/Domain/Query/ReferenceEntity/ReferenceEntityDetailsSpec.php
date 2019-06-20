<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use PhpSpec\ObjectBehavior;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;

class ReferenceEntityDetailsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityDetails::class);
    }

    function it_normalizes_a_read_model(Image $image, AttributeDetails $name)
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
        $this->recordCount = 123;
        $this->attributes = [
            $name
        ];
        $this->isAllowedToEdit = false;
        $this->attributeAsLabel = AttributeAsLabelReference::noReference();
        $this->attributeAsImage = AttributeAsImageReference::noReference();

        $name->normalize()->willReturn(['code' => 'name']);

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
                ],
                'record_count' => 123,
                'attributes' => [
                    [
                        'code' => 'name'
                    ]
                ],
                'permission' => [
                    'edit' => false
                ],
                'attribute_as_label' => null,
                'attribute_as_image' => null,
            ]
        );
    }
}

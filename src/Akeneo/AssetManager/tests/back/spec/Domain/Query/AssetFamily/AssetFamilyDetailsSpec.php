<?php

namespace spec\Akeneo\AssetManager\Domain\Query\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use PhpSpec\ObjectBehavior;

class AssetFamilyDetailsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetFamilyDetails::class);
    }

    function it_normalizes_a_read_model(Image $image, AttributeDetails $name)
    {
        $image->normalize()->willReturn([
            'filePath'         => '/path/image.jpg',
            'originalFilename' => 'image.jpg'
        ]);

        $this->identifier = AssetFamilyIdentifier::fromString('starck');
        $this->labels = LabelCollection::fromArray([
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ]);
        $this->image = $image;
        $this->assetCount = 123;
        $this->attributes = [
            $name
        ];
        $this->transformations = new ConnectorTransformationCollection([]);
        $this->isAllowedToEdit = false;
        $this->attributeAsLabel = AttributeAsLabelReference::noReference();
        $this->attributeAsMainMedia = AttributeAsMainMediaReference::noReference();
        $this->namingConvention = NamingConvention::createFromNormalized([]);
        $this->productLinkRules = ['rule'];

        $name->normalize()->willReturn(['code' => 'name']);

        $this->normalize()->shouldBeLike(
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
                'asset_count' => 123,
                'attributes' => [
                    [
                        'code' => 'name'
                    ]
                ],
                'permission' => [
                    'edit' => false
                ],
                'attribute_as_label' => null,
                'attribute_as_main_media' => null,
                'transformations' => [],
                'naming_convention' => new \stdClass,
                'product_link_rules' => ['rule'],
            ]
        );
    }
}

<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->beConstructedWith(
            $normalizer,
            $denormalizer,
            ['.', ','],
            [
                ['value' => 'yyyy-MM-dd', 'label' => 'yyyy-mm-dd'],
                ['value' => 'dd.MM.yyyy', 'label' => 'dd.mm.yyyy'],
            ],
            'upload/path/',
            'csv'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn(
            [
                'decimalSeparator' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => ['.', ','],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.decimalSeparator.label',
                        'help'     => 'pim_base_connector.export.decimalSeparator.help'
                    ]
                ],
                'dateFormat' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            ['value' => 'yyyy-MM-dd', 'label' => 'yyyy-mm-dd'],
                            ['value' => 'dd.MM.yyyy', 'label' => 'dd.mm.yyyy'],
                        ],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.dateFormat.label',
                        'help'     => 'pim_base_connector.export.dateFormat.help'
                    ]
                ],
            ]
        );
    }

    function it_processes_variant_group_without_product_template(
        $normalizer,
        GroupInterface $variantGroup
    ) {
        $variantGroup->getProductTemplate()->willReturn(null);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);
    }

    function it_processes_variant_group_without_media(
        $normalizer,
        $denormalizer,
        ArrayCollection $productValuesCollection,
        ArrayCollection $emptyCollection,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue
    ) {
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($emptyCollection);
        $emptyCollection->toArray()->willReturn([]);

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);
    }

    function it_processes_a_variant_group_with_several_media(
        $normalizer,
        $denormalizer,
        ArrayCollection $productValuesCollection,
        ArrayCollection $mediaCollection,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue
    ) {
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($mediaCollection);
        $mediaCollection->toArray()->willReturn([$media1, $media2]);

        $normalizer->normalize(
            [$media1, $media2],
            'csv',
            [
                'field_name'   => 'media',
                'prepare_copy' => true,
                'identifier'   => 'my_variant_group'
            ]
        )->willReturn([
            ['code' => 'img', 'path' => 'upload/path/', 'ext' => 'jpg'],
            ['code' => 'yolo_img', 'path' => 'upload/path/', 'ext' => 'jpg']
        ]);

        $normalizer->normalize(
            $variantGroup,
            'csv',
            [
                'with_variant_group_values' => true,
                'identifier'                => 'my_variant_group',
                'decimal_separator'         => '.',
                'date_format'                => 'yyyy-MM-dd',
            ]
        )->willReturn('my;variant;group;to;csv;');

        $this->process($variantGroup)->shouldReturn([
            'media' => [
                ['code' => 'img', 'path' => 'upload/path/', 'ext' => 'jpg'],
                ['code' => 'yolo_img', 'path' => 'upload/path/', 'ext' => 'jpg']
            ],
            'variant_group' => 'my;variant;group;to;csv;'
        ]);
    }

    function it_throws_an_exception_if_media_of_variant_group_is_not_found(
        $normalizer,
        $denormalizer,
        ArrayCollection $productValuesCollection,
        ArrayCollection $mediaCollection,
        FileInfoInterface $media,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        ProductValueInterface $productValue
    ) {
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getCode()->willReturn('my_variant_group');

        $productTemplate->getValuesData()->willReturn([$productValue]);

        $denormalizer->denormalize([$productValue], 'ProductValue[]', 'json')->willReturn($productValuesCollection);

        $productValuesCollection->filter(Argument::cetera())->willReturn($mediaCollection);
        $mediaCollection->toArray()->willReturn([$media]);

        $normalizer->normalize(
            [$media],
            'csv',
            [
                'field_name'   => 'media',
                'prepare_copy' => true,
                'identifier'   => 'my_variant_group'
            ]
        )->willThrow(new FileNotFoundException('upload/path/img.jpg'));

        $this->shouldThrow(
            new InvalidItemException(
                'The file "upload/path/img.jpg" does not exist',
                [
                    'item'            => 'my_variant_group',
                    'uploadDirectory' => 'upload/path/'
                ]
            )
        )->duringProcess($variantGroup);
    }
}

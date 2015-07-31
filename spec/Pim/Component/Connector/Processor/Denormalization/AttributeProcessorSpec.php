<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeFactory $attributeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $repository,
            $attributeFactory,
            $updater,
            $validator
        );
    }

    function it_processes_items(
        $arrayConverter,
        $attributeFactory,
        $updater,
        $repository,
        $validator,
        AttributeInterface $attribute
    ) {
        $item = [
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'label-de_DE'            => 'SKU',
            'label-en_US'            => 'SKU',
            'label-fr_FR'            => 'SKU',
            'unique'                 => '1',
            'useable_as_grid_filter' => '1',
        ];

        $convertedItems =
            [
                'labels'                 => [
                    'de_DE' => 'SKU',
                    'en_US' => 'SKU',
                    'fr_FR' => 'SKU',
                ],
                'attributeType'          => 'pim_catalog_identifier',
                'code'                   => 'sku',
                'unique'                 => true,
                'useable_as_grid_filter' => true,
            ];

        $arrayConverter->convert($item)->willReturn($convertedItems);

        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sku')->willReturn(null);
        $attributeFactory->createAttribute('pim_catalog_identifier')->willReturn($attribute);
        $updater->update($attribute, $convertedItems)->shouldBeCalled();
        $validator->validate($attribute)->willReturn(new ConstraintViolationList());

        $this->process($item)->shouldReturn($attribute);
    }

    function it_throws_an_exception_if_attribute_is_invalid(
        $arrayConverter,
        $attributeFactory,
        $updater,
        $repository,
        AttributeInterface $attribute
    ) {
        $item = [
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'label-de_DE'            => 'SKU',
            'label-en_US'            => 'SKU',
            'label-fr_FR'            => 'SKU',
            'unique'                 => '1',
            'useable_as_grid_filter' => '1',
        ];

        $convertedItems =
            [
                'labels'                 => [
                    'de_DE' => 'SKU',
                    'en_US' => 'SKU',
                    'fr_FR' => 'SKU',
                ],
                'attributeType'          => 'pim_catalog_identifier',
                'code'                   => 'sku',
                'unique'                 => true,
                'useable_as_grid_filter' => true,
            ];

        $arrayConverter->convert($item)->willReturn($convertedItems);

        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sku')->willReturn(null);
        $attributeFactory->createAttribute('pim_catalog_identifier')->willReturn($attribute);
        $updater->update($attribute, $convertedItems)->willThrow('\InvalidArgumentException');

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('process', [$item]);
    }
}

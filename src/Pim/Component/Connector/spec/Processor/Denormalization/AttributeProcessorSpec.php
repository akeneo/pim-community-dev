<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        AttributeFactory $attributeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $repository,
            $attributeFactory,
            $updater,
            $validator
        );
    }

    function it_processes_items(
        $attributeFactory,
        $updater,
        $repository,
        $validator,
        AttributeInterface $attribute
    ) {
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

        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sku')->willReturn(null);
        $attributeFactory->createAttribute('pim_catalog_identifier')->willReturn($attribute);
        $updater->update($attribute, $convertedItems)->shouldBeCalled();
        $validator->validate($attribute)->willReturn(new ConstraintViolationList());

        $this->process($convertedItems)->shouldReturn($attribute);
    }

    function it_throws_an_exception_if_attribute_is_invalid(
        $attributeFactory,
        $updater,
        $repository,
        AttributeInterface $attribute
    ) {
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

        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sku')->willReturn(null);
        $attributeFactory->createAttribute('pim_catalog_identifier')->willReturn($attribute);
        $updater->update($attribute, $convertedItems)->willThrow('\InvalidArgumentException');

        $this->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')->during('process', [$convertedItems]);
    }
}

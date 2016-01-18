<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        ManagerRegistry $managerRegistry
    ) {
        $this->beConstructedWith(
            $validator,
            $translator,
            $transformer,
            $managerRegistry,
            'Pim\Bundle\CatalogBundle\Model\Product',
            false
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\ProductProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_is_configurable()
    {
        $this->isEnabled()->shouldReturn(true);
        $this->getCategoriesColumn()->shouldReturn('categories');
        $this->getGroupsColumn()->shouldReturn('groups');
        $this->getFamilyColumn()->shouldReturn('family');

        $this->setEnabled(false);
        $this->setCategoriesColumn('my_category_column');
        $this->setGroupsColumn('my_group_column');
        $this->setFamilyColumn('my_family_column');

        $this->isEnabled()->shouldReturn(false);
        $this->getCategoriesColumn()->shouldReturn('my_category_column');
        $this->getGroupsColumn()->shouldReturn('my_group_column');
        $this->getFamilyColumn()->shouldReturn('my_family_column');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'enabled' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.enabled.label',
                    'help'  => 'pim_base_connector.import.enabled.help'
                ]
            ],
            'categoriesColumn' => [
                'options' => [
                    'label' => 'pim_base_connector.import.categoriesColumn.label',
                    'help'  => 'pim_base_connector.import.categoriesColumn.help'
                ]
            ],
            'familyColumn' => [
                'options' => [
                    'label' => 'pim_base_connector.import.familyColumn.label',
                    'help'  => 'pim_base_connector.import.familyColumn.help'
                ]
            ],
            'groupsColumn' => [
                'options' => [
                    'label' => 'pim_base_connector.import.groupsColumn.label',
                    'help'  => 'pim_base_connector.import.groupsColumn.help'
                ]
            ],
        ]);
    }

    function it_processes_an_item_without_mapping($transformer, $validator, ProductInterface $product)
    {
        $item = [
            'sku' => 'AKNTS',
            'family' => 'tshirts',
            'groups' => 'akeneo_tshirt',
            'categories' => 'tshirts,goodies',
            'SUBSTITUTION-groups' => '',
            'SUBSTITUTION-products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS',
            'description-en_US-mobile' => '<p>Akeneo T-Shirt</p>'
        ];

        $transformer
            ->transform('Pim\Bundle\CatalogBundle\Model\Product', $item, ['enabled' => true])
            ->willReturn($product);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([]);

        $validator->validate($product, [], $item, [])->willReturn([]);

        $this->process($item)->shouldReturn($product);
    }

    function it_processes_an_item_with_mapping($transformer, $validator, ProductInterface $product)
    {
        $item = [
            'sku' => 'AKNTS',
            'family' => 'tshirts',
            'groups' => 'akeneo_tshirt',
            'categories' => 'tshirts,goodies',
            'SUBSTITUTION-groups' => '',
            'SUBSTITUTION-products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS',
            'description-en_US-mobile' => '<p>Akeneo T-Shirt</p>'
        ];
        $mappedItem = [
            'sku' => 'AKNTS',
            'family' => 'tshirts',
            'groups' => 'akeneo_tshirt',
            'categories' => 'tshirts,goodies',
            'SUBSTITUTION Groups' => '',
            'SUBSTITUTION Products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS',
            'description-en_US-mobile' => '<p>Akeneo T-Shirt</p>'
        ];

        $this->addMapping('SUBSTITUTION-products', 'SUBSTITUTION Products');
        $this->addMapping('SUBSTITUTION-groups', 'SUBSTITUTION Groups');

        $transformer->transform('Pim\Bundle\CatalogBundle\Model\Product', $mappedItem, ['enabled' => true])->willReturn($product);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([]);

        $validator->validate($product, [], $mappedItem, [])->willReturn([]);

        $this->process($item)->shouldReturn($product);
    }

    function it_throws_an_exception_if_an_error_occurs_during_processing(
        $transformer,
        $validator,
        $managerRegistry,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ObjectManager $objectManager,
        StepExecution $stepExecution
    ) {
        $item = [
            'sku' => 'AKNTS',
            'family' => 'tshirts',
            'groups' => 'akeneo_tshirt',
            'categories' => 'tshirts,goodies',
            'SUBSTITUTION-groups' => '',
            'SUBSTITUTION-products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS',
            'description-en_US-mobile' => '<p>Akeneo T-Shirt</p>',
            'not_empty_attribute' => ''
        ];

        $transformer->transform('Pim\Bundle\CatalogBundle\Model\Product', $item, ['enabled' => true])->willReturn($product);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Model\Product')->willReturn([$columnInfo]);

        $validator
            ->validate($product, [$columnInfo], $item, [])
            ->willReturn([
                'AKNTS' => [["The value \"\" for not empty attribute \"not_empty_attribute\" is empty"]]
            ]);

        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($objectManager);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->shouldThrow(
            new InvalidItemException(
                'AKNTS: ',
                $item
            )
        )->duringProcess($item);
    }
}

<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class CategoryProcessorSpec extends ObjectBehavior
{
    function let(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        ManagerRegistry $managerRegistry,
        DoctrineCache $doctrineCache
    ) {
        $this->beConstructedWith(
            $validator,
            $translator,
            $transformer,
            $managerRegistry,
            'Pim\Bundle\CatalogBundle\Entity\Category',
            $doctrineCache
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\CategoryProcessor');
    }

    function it_is_an_item_processor_step_execution_aware()
    {
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'circularRefsChecked' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.circularRefsChecked.label',
                    'help'  => 'pim_base_connector.import.circularRefsChecked.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->isCircularRefsChecked()->shouldReturn(true);

        $this->setCircularRefsChecked(false);

        $this->isCircularRefsChecked()->shouldReturn(false);
    }

    function it_processes_categories_with_parents(
        $transformer,
        $validator,
        $doctrineCache,
        CategoryInterface $mugsCategory,
        CategoryInterface $tShirtsCategory,
        CategoryInterface $parentCategory,
        CategoryInterface $rootCategory
    ) {
        $itemCatMugs = [
            'id'      => 10,
            'code'    => 'my_category_mugs',
            'parent'  => 'my_category_root',
            'created' => 'date',
            'root'    => 1
        ];
        $itemCatRoot = [
            'id'      => 1,
            'code'    => 'my_category_root',
            'created' => 'date2',
            'root'    => 1
        ];
        $itemCatTShirts = [
            'id'      => 12,
            'code'    => 'my_category_tshirts',
            'parent'  => 'my_category_parent',
            'created' => 'date2',
            'root'    => 1
        ];
        $data = [$itemCatMugs, $itemCatRoot, $itemCatTShirts];

        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 10,
                'code'    => 'my_category_mugs',
                'created' => 'date',
                'root'    => 1
            ]
        )->willReturn($mugsCategory);
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'my_category_tshirts',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($tShirtsCategory);
        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $itemCatRoot)->willReturn($rootCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate(Argument::cetera())->willReturn([]);

        $doctrineCache
            ->find('Pim\Bundle\CatalogBundle\Entity\Category', 'my_category_parent')
            ->willReturn($parentCategory);

        $mugsCategory->setParent($rootCategory)->shouldBeCalled();
        $tShirtsCategory->setParent($parentCategory)->shouldBeCalled();

        $mugsCategory->getCode()->willReturn('my_category_mugs');
        $mugsCategory->getParent()->willReturn($rootCategory);

        $rootCategory->getCode()->willReturn('my_category_root');
        $rootCategory->getParent()->willReturn(null);

        $tShirtsCategory->getCode()->willReturn('my_category_tshirts');
        $tShirtsCategory->getParent()->willReturn($parentCategory);

        $parentCategory->getCode()->willReturn('my_category_parent');
        $parentCategory->getParent()->willReturn(null);

        $this->process($data)->shouldReturn([
            'my_category_mugs'    => $mugsCategory,
            'my_category_root'    => $rootCategory,
            'my_category_tshirts' => $tShirtsCategory
        ]);
    }

    function it_processes_a_category_with_a_non_existing_parent_and_a_step_execution(
        $transformer,
        $validator,
        $doctrineCache,
        CategoryInterface $mugsCategory,
        CategoryInterface $tShirtsCategory,
        CategoryInterface $rootCategory,
        StepExecution $stepExecution
    ) {
        $itemCatMugs = [
            'id'      => 10,
            'code'    => 'my_category_mugs',
            'parent'  => 'my_category_root',
            'created' => 'date',
            'root'    => 1
        ];
        $itemCatRoot = [
            'id'      => 1,
            'code'    => 'my_category_root',
            'created' => 'date2',
            'root'    => 1
        ];
        $itemCatTShirts = [
            'id'      => 12,
            'code'    => 'my_category_tshirts',
            'parent'  => 'my_category_parent',
            'created' => 'date2',
            'root'    => 1
        ];
        $data = [$itemCatMugs, $itemCatRoot, $itemCatTShirts];

        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 10,
                'code'    => 'my_category_mugs',
                'created' => 'date',
                'root'    => 1
            ]
        )->willReturn($mugsCategory);
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'my_category_tshirts',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($tShirtsCategory);
        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $itemCatRoot)->willReturn($rootCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate(Argument::cetera())->willReturn([]);

        $doctrineCache->find('Pim\Bundle\CatalogBundle\Entity\Category', 'my_category_parent')->willReturn(null);

        $mugsCategory->setParent($rootCategory)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution
            ->addWarning(
                'category_processor',
                'parent: ',
                [],
                [
                    'id' => 12,
                    'code' => 'my_category_tshirts',
                    'created' => 'date2',
                    'root' => 1
                ]
            )
            ->shouldBeCalled();

        $mugsCategory->getCode()->willReturn('my_category_mugs');
        $mugsCategory->getParent()->willReturn($rootCategory);

        $rootCategory->getCode()->willReturn('my_category_root');
        $rootCategory->getParent()->willReturn(null);

        $this->setStepExecution($stepExecution);
        $this->process($data)->shouldReturn([
            'my_category_mugs' => $mugsCategory,
            'my_category_root' => $rootCategory
        ]);
    }

    function it_processes_a_category_with_a_non_existing_parent_and_no_step_execution(
        $transformer,
        $validator,
        $doctrineCache,
        CategoryInterface $mugsCategory,
        StepExecution $stepExecution
    ) {
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'my_category_mugs',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($mugsCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate(
            $mugsCategory,
            [],
            [
                'id'      => 12,
                'code'    => 'my_category_mugs',
                'created' => 'date2',
                'root'    => 1
            ],
            []
        )->willReturn([]);

        $doctrineCache->find('Pim\Bundle\CatalogBundle\Entity\Category', 'my_category_parent')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldNotBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(
            new InvalidItemException(
                'parent: ',
                [
                    'id'      => 12,
                    'code'    => 'my_category_mugs',
                    'created' => 'date2',
                    'root'    => 1
                ]
            )
        )->duringProcess([
            [
                'id'      => 12,
                'code'    => 'my_category_mugs',
                'parent'  => 'my_category_parent',
                'created' => 'date2',
                'root'    => 1
            ]
        ]);
    }

    function it_processes_categories_with_validation_error(
        $transformer,
        $validator,
        $managerRegistry,
        CategoryInterface $mugsCategory,
        CategoryInterface $tShirtsCategory,
        CategoryInterface $rootCategory,
        ObjectManager $objectManager,
        StepExecution $stepExecution
    ) {
        $itemCatMugs = [
            'id'      => 10,
            'code'    => 'my_category_mugs',
            'parent'  => 'my_category_root',
            'created' => 'date',
            'root'    => 1
        ];
        $itemCatRoot = [
            'id'      => 1,
            'code'    => 'my_category_root',
            'created' => 'date2',
            'root'    => 1
        ];
        $itemCatTShirts = [
            'id'      => 12,
            'code'    => 'my_category_tshirts',
            'parent'  => 'my_category_parent',
            'created' => 'date2',
            'root'    => 1
        ];
        $data = [$itemCatMugs, $itemCatRoot, $itemCatTShirts];

        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 10,
                'code'    => 'my_category_mugs',
                'created' => 'date',
                'root'    => 1
            ]
        )->willReturn($mugsCategory);
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'my_category_tshirts',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($tShirtsCategory);
        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $itemCatRoot)->willReturn($rootCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate($mugsCategory, Argument::cetera())->willReturn([]);
        $validator->validate($rootCategory, Argument::cetera())->willReturn([]);
        $validator->validate($tShirtsCategory, Argument::cetera())->willReturn([
            'attribute' => [
                [
                    'Attribute with code %code% has error',
                    ['%code%' => 'errored_attribute']
                ]
            ]
        ]);

        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($objectManager);
        $objectManager->detach($tShirtsCategory)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution
            ->addWarning(
                'category_processor',
                'attribute: ',
                [],
                [
                    'id' => 12,
                    'code' => 'my_category_tshirts',
                    'created' => 'date2',
                    'root' => 1
                ]
            )
            ->shouldBeCalled();

        $mugsCategory->getCode()->willReturn('my_category_mugs');
        $mugsCategory->getParent()->willReturn($rootCategory);
        $mugsCategory->setParent($rootCategory)->shouldBeCalled();

        $rootCategory->getCode()->willReturn('my_category_root');
        $rootCategory->getParent()->willReturn(null);

        $this->setStepExecution($stepExecution);
        $this->process($data)->shouldReturn([
            'my_category_mugs' => $mugsCategory,
            'my_category_root' => $rootCategory
        ]);
    }

    function it_processes_categories_with_validation_error_and_no_step_execution(
        $transformer,
        $validator,
        $managerRegistry,
        CategoryInterface $mugsCategory,
        ObjectManager $objectManager,
        StepExecution $stepExecution
    ) {
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'my_category_mugs',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($mugsCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate($mugsCategory, Argument::cetera())->willReturn([
            'attribute' => [
                [
                    'Attribute with code %code% has error',
                    ['%code%' => 'errored_attribute']
                ]
            ]
        ]);

        $managerRegistry->getManagerForClass(Argument::type('string'))->willReturn($objectManager);
        $objectManager->detach($mugsCategory)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::cetera())->shouldNotBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(
            new InvalidItemException(
                'attribute: ',
                [
                    'id' => 12,
                    'code' => 'my_category_mugs',
                    'created' => 'date2',
                    'root' => 1
                ]
            )
        )->duringProcess([
            [
                'id'      => 12,
                'code'    => 'my_category_mugs',
                'parent'  => 'my_category_parent',
                'created' => 'date2',
                'root'    => 1
            ]
        ]);
    }

    function it_skips_and_adds_warning_if_there_are_categories_with_circular_references(
        $transformer,
        $validator,
        CategoryInterface $childCategory,
        CategoryInterface $parentCategory,
        CategoryInterface $grandParentCategory,
        StepExecution $stepExecution
    ) {
        $item1 = [
            'id'      => 11,
            'code'    => 'child_category',
            'parent'  => 'parent_category',
            'created' => 'date',
            'root'    => 1
        ];
        $item2 = [
            'id'      => 12,
            'code'    => 'parent_category',
            'parent'  => 'grand_parent_category',
            'created' => 'date2',
            'root'    => 1
        ];
        $item3 = [
            'id'      => 13,
            'code'    => 'grand_parent_category',
            'parent'  => 'child_category',
            'created' => 'date2',
            'root'    => 1
        ];
        $data = [$item1, $item2, $item3];

        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 11,
                'code'    => 'child_category',
                'created' => 'date',
                'root'    => 1
            ]
        )->willReturn($childCategory);
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 12,
                'code'    => 'parent_category',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($parentCategory);
        $transformer->transform(
            'Pim\Bundle\CatalogBundle\Entity\Category',
            [
                'id'      => 13,
                'code'    => 'grand_parent_category',
                'created' => 'date2',
                'root'    => 1
            ]
        )->willReturn($grandParentCategory);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate(Argument::cetera())->willReturn([]);

        $childCategory->setParent($parentCategory)->shouldBeCalled();
        $parentCategory->setParent($grandParentCategory)->shouldBeCalled();
        $grandParentCategory->setParent($childCategory)->shouldBeCalled();

        $childCategory->getCode()->willReturn('child_category');
        $childCategory->getParent()->willReturn($parentCategory);

        $parentCategory->getCode()->willReturn('parent_category');
        $parentCategory->getParent()->willReturn($grandParentCategory);

        $grandParentCategory->getCode()->willReturn('grand_parent_category');
        $grandParentCategory->getParent()->willReturn($childCategory);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(3);
        $stepExecution
            ->addWarning(
                'category_processor',
                'parent: ',
                [],
                [
                    'id'      => 11,
                    'code'    => 'child_category',
                    'created' => 'date',
                    'root'    => 1
                ]
            )
            ->shouldBeCalled();
        $stepExecution
            ->addWarning(
                'category_processor',
                'parent: ',
                [],
                [
                    'id'      => 12,
                    'code'    => 'parent_category',
                    'created' => 'date2',
                    'root'    => 1
                ]
            )
            ->shouldBeCalled();
        $stepExecution
            ->addWarning(
                'category_processor',
                'parent: ',
                [],
                [
                    'id'      => 13,
                    'code'    => 'grand_parent_category',
                    'created' => 'date2',
                    'root'    => 1
                ]
            )
            ->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->process($data)->shouldReturn([]);
    }
}

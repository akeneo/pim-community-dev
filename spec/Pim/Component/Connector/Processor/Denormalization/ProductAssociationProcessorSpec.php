<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ProductFilterInterface $productAssocFilter
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $productRepository,
            $productUpdater,
            $productValidator,
            $productAssocFilter
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldHaveCount(1);
    }

    function it_updates_an_existing_product(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productAssocFilter,
        ProductInterface $product,
        AssociationInterface $association,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku'           => 'tshirt',
            'XSELL-groups'  => ['akeneo_tshirt, oro_tshirt'],
            'XSELL-product' => ['AKN_TS, ORO_TSH']
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];
        $arrayConverter
            ->convert($originalData, ["with_associations" => true])
            ->willReturn($convertedData);

        $preFilteredData = $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        unset($filteredData['associations']['XSELL']['groups']);
        $productAssocFilter->filter($product, $preFilteredData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $product->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }
    
    function it_skips_a_product_when_update_fails(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku'               => 'tshirt',
            'NOT_FOUND-groups'  => ['akeneo_tshirt, oro_tshirt'],
            'NOT_FOUND-product' => ['AKN_TS, ORO_TSH']
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];
        $arrayConverter
            ->convert($originalData, ["with_associations" => true])
            ->willReturn($convertedData);

        $filteredData = [
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new \InvalidArgumentException('association does not exists'));

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_association_is_invalid(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productAssocFilter,
        $stepExecution,
        AssociationInterface $association,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku'           => 'tshirt',
            'XSELL-groups'  => ['akeneo_tshirt, oro_tshirt'],
            'XSELL-product' => ['AKN_TS, ORO_TSH']
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];
        $arrayConverter
            ->convert($originalData, ["with_associations" => true])
            ->willReturn($convertedData);

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $product->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violations);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku'           => 'tshirt',
            'XSELL-groups'  => ['akeneo_tshirt, oro_tshirt'],
            'XSELL-product' => ['AKN_TS, ORO_TSH']
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];
        $arrayConverter
            ->convert($originalData, ["with_associations" => true])
            ->willReturn($convertedData);

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $filteredData)->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $this->process($originalData)
            ->shouldReturn(null);
    }
}

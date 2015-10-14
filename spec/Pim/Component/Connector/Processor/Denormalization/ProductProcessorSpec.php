<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher,
        ProductFilterInterface $productFilter
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $productRepository,
            $productBuilder,
            $productUpdater,
            $productValidator,
            $productDetacher,
            $productFilter
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_extra_configuration()
    {
        $this->getConfigurationFields()->shouldHaveCount(5);
    }

    function it_updates_an_existing_product(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_with_filtered_values(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $preFilteredData = $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        unset($filteredData['family'], $filteredData['name'][1]);
        $productFilter->filter($product, $preFilteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_without_filtered_values(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        $productFilter->filter($product, [])->shouldNotBeCalled();

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this->setEnabledComparison(false);
        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_creates_a_product(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_skips_a_product_when_identifier_is_empty(
        $arrayConverter,
        $productRepository
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);

        $originalData = [
            'sku' => '',
            'family' => 'TShirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => null
                ],
            ],
            'family' => 'Tshirt',
        ];

        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_update_fails(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productDetacher,
        $productFilter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new \InvalidArgumentException('family does not exists'));

        $productDetacher->detach($product)->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_object_is_invalid(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productDetacher,
        $productFilter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $productValidator
            ->validate($product)
            ->willReturn($violations);

        $productDetacher->detach($product);

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
        $productFilter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);
        $product->getId()->willReturn(1);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            "mapping" => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values" => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)->shouldNotBeCalled();

        $this
            ->process($originalData)
            ->shouldReturn(null);
    }

    function it_creates_a_product_with_sku_and_family_columns(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku'    => 'tshirt',
            'family' => 'TShirt',
        ];
        $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
        ];
        $converterOptions = [
            "mapping"           => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values"    => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
        ];

        $productFilter->filter($product, $filteredData)->shouldNotBeCalled();

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }
}

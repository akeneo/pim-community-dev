<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $productRepository,
            $productBuilder,
            $productUpdater,
            $productValidator,
            $productDetacher
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
        $this->getConfigurationFields()->shouldHaveCount(4);
    }

    function it_updates_an_existing_product(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
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
            "default_values" => ["enabled" => true]
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

    function it_creates_a_product(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
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
            "default_values" => ["enabled" => true]
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

    function it_skips_a_product_when_update_fails(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productDetacher,
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
            "default_values" => ["enabled" => true]
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
            "default_values" => ["enabled" => true]
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
}

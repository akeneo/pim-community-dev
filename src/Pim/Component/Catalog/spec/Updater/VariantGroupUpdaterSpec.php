<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class VariantGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        $productTemplateClass,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $groupTypeRepository,
            $productBuilder,
            $productUpdater,
            $pqbFactory,
            $productTemplateClass
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\VariantGroupUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_variant_group()
    {
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Component\Catalog\Model\GroupInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
        );
    }

    function it_updates_a_variant_group(
        $attributeRepository,
        $groupTypeRepository,
        $productBuilder,
        $pqbFactory,
        GroupInterface $variantGroup,
        AttributeInterface $attribute,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        ProductInterface $product,
        ProductInterface $removedProduct,
        ProductInterface $addedProduct,
        ProductTemplateInterface $productTemplate,
        ProductQueryBuilderInterface $pqb
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('secondary_color')->willReturn($attribute);
        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('id', 'IN', [2])->shouldBeCalled();
        $pqb->execute()->willReturn([$addedProduct]);

        $variantGroup->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setLocale('fr_FR')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);
        $variantGroup->addAxisAttribute(Argument::any())->shouldBeCalled();

        $variantGroup->removeProduct($removedProduct)->shouldBeCalled();
        $variantGroup->addProduct($addedProduct)->shouldBeCalled();
        $variantGroup->getProducts()->willReturn([$removedProduct]);

        $productTemplate->getValuesData()->willReturn([]);
        $productTemplate->setValues(Argument::any())->shouldBeCalled();
        $productTemplate->setValuesData(['main_color' => [['locale' => null, 'scope' => null, 'data' => 'white']]])
            ->shouldBeCalled();
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();

        $productValue = new ProductValue();
        $identifierValue = new ProductValue();

        $productBuilder->createProduct()->willReturn($product);
        $product->getValues()->willReturn(new ArrayCollection([$productValue, $identifierValue]));
        $product->getIdentifier()->willReturn($identifierValue);

        $values = [
            'code'         => 'mycode',
            'axis'         => ['main_color', 'secondary_color'],
            'type'         => 'VARIANT',
            'labels'       => [
                'fr_FR' => 'T-shirt super beau',
            ],
            'values' => [
                'main_color'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'white',
                    ]
                ]
            ],
            'products' => [2]
        ];

        $this->update($variantGroup, $values, []);
    }

    function it_updates_an_empty_variant_group(
        $groupTypeRepository,
        $productBuilder,
        $pqbFactory,
        GroupInterface $variantGroup,
        GroupTypeInterface $type,
        ProductInterface $product,
        ProductTemplateInterface $productTemplate
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $pqbFactory->create()->shouldNotBeCalled();

        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->setProductTemplate($productTemplate)->shouldBeCalled();

        $variantGroup->getId()->willReturn(null);
        $variantGroup->getProducts()->willReturn([]);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);

        $productTemplate->getValuesData()->willReturn([]);
        $productTemplate->setValues(Argument::any())->shouldBeCalled();
        $productTemplate->setValuesData([])->shouldBeCalled();

        $productValue = new ProductValue();
        $identifierValue = new ProductValue();

        $productBuilder->createProduct()->willReturn($product);
        $product->getValues()->willReturn(new ArrayCollection([$productValue, $identifierValue]));
        $product->getIdentifier()->willReturn($identifierValue);

        $values = [
            'code'     => 'mycode',
            'axis'     => [],
            'type'     => 'VARIANT',
            'labels'   => [],
            'values'   => [],
            'products' => []
        ];

        $this->update($variantGroup, $values, []);
    }

    function it_throws_an_error_if_type_is_unknown(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'type' => 'UNKNOWN',
        ];

        $this->shouldThrow(new \InvalidArgumentException('Type "UNKNOWN" does not exist'))
            ->during('update', [$variantGroup, $values, []]);
    }

    function it_throws_an_error_if_axis_is_unknown(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'axis' => ['unknown', 'secondary_color'],
        ];

        $this->shouldThrow(new \InvalidArgumentException('Attribute "unknown" does not exist'))
            ->during('update', [$variantGroup, $values, []]);
    }

    function it_throws_an_error_if_axis_is_updated(GroupInterface $variantGroup)
    {
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->getId()->willReturn(42);
        $attribute = new Attribute();
        $attribute->setCode('other');
        $variantGroup->getAxisAttributes()->willReturn(new ArrayCollection([$attribute]));

        $values = [
            'code' => 'mycode',
            'axis' => ['main_color'],
        ];

        $this->shouldThrow(new \InvalidArgumentException('Attributes: This property cannot be changed.'))
            ->during('update', [$variantGroup, $values, []]);
    }

    function it_merges_original_and_new_values(
        GroupInterface $variantGroup,
        ProductTemplateInterface $template,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product,
        ProductValueInterface $identifier,
        ArrayCollection $values,
        \Iterator $valuesIterator
    ) {
        $originalValues = [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'original description en_US'
                ],
                [
                    'locale' => 'de_DE',
                    'scope'  => 'ecommerce',
                    'data'   => 'original description de_DE'
                ]
            ],
            'image' => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'originalFilename',
                        'filePath' => 'originalFilepath',
                        'hash' => 'originalhash',
                    ]
                ]
            ]
        ];

        $newValues = [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'new description en_US'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'data'   => 'new description fr_FR'
                ]

            ],
            'image' => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'originalFilename',
                        'filePath' => 'originalFilepath',
                        'hash' => 'originalhash',
                    ]
                ],
                [
                    'locale' => 'de_DE',
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'newFilename',
                        'filePath' => 'newFilepath',
                        'hash' => 'newhash',
                    ]
                ]
            ]
        ];

        $expectedValues = [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'new description en_US'
                ],
                [
                    'locale' => 'de_DE',
                    'scope'  => 'ecommerce',
                    'data'   => 'original description de_DE'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'data'   => 'new description fr_FR'
                ]
            ],
            'image' => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'originalFilename',
                        'filePath' => 'originalFilepath',
                        'hash' => 'originalhash',
                    ]
                ],
                [
                    'locale' => 'de_DE',
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'newFilename',
                        'filePath' => 'newFilepath',
                        'hash' => 'newhash',
                    ]
                ]
            ]
        ];

        $variantGroup->getProductTemplate()->willReturn($template);
        $template->getValuesData()->willReturn($originalValues);

        $productBuilder->createProduct()->willReturn($product);
        $product->getValues()->willReturn($values);
        $product->getIdentifier()->willReturn($identifier);
        $values->removeElement($identifier)->shouldBeCalled();

        $values->getIterator()->willReturn($valuesIterator);

        $template->setValues($values)->shouldBeCalled();
        $template->setValuesData($expectedValues)->shouldBeCalled();
        $variantGroup->setProductTemplate($template)->shouldBeCalled();

        $this->setValues($variantGroup, $newValues);
    }

}

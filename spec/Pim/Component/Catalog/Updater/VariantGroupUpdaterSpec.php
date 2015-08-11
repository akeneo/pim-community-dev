<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class VariantGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        $productTemplateClass
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $groupTypeRepository,
            $productBuilder,
            $productUpdater,
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
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
        );
    }

    function it_updates_a_variant_group(
        $attributeRepository,
        $groupTypeRepository,
        $productBuilder,
        GroupInterface $variantGroup,
        AttributeInterface $attribute,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        ProductInterface $product,
        ProductTemplateInterface $productTemplate
    ) {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($type);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('secondary_color')->willReturn($attribute);

        $variantGroup->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $variantGroup->setCode('mycode')->shouldBeCalled();
        $variantGroup->setLocale('fr_FR')->shouldBeCalled();
        $variantGroup->setType($type)->shouldBeCalled();
        $variantGroup->getId()->willReturn(null);
        $variantGroup->addAxisAttribute(Argument::any())->shouldBeCalled();

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
            ]
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
}

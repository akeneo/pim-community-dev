<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\View;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\VariantViewUpdater;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterRegistry;
use Symfony\Component\Form\FormView;

class ProductFormViewSpec extends ObjectBehavior
{
    function let(ViewUpdaterRegistry $viewUpdaterRegistry)
    {
        $this->beConstructedWith($viewUpdaterRegistry);
    }

    function it_adds_a_product_value_child(
        ProductValueInterface $value,
        AttributeInterface $attribute,
        AttributeGroup $group,
        FormView $valueFormView,
        $viewUpdaterRegistry,
        VariantViewUpdater $variantViewUpdater
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->isRemovable()->willReturn(true);
        $value->getLocale()->willReturn(null);
        $value->getEntity()->willReturn(null);
        $attribute->getGroup()->willReturn($group);
        $attribute->getId()->willReturn(42);
        $attribute->getCode()->willReturn('name');
        $attribute->getLabel()->willReturn('Name');
        $attribute->getSortOrder()->willReturn(10);
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('general');
        $group->getLabel()->willReturn('General');

        $this->addChildren($value, $valueFormView);

        $viewUpdaterRegistry->getUpdaters()->willReturn([$variantViewUpdater]);

        $nameAttributeView = [
            'id'                 => 42,
            'isRemovable'        => true,
            'code'               => 'name',
            'label'              => 'Name',
            'sortOrder'          => 10,
            'allowValueCreation' => false,
            'locale'             => null,
            'value'              => $valueFormView,
        ];

        $variantViewUpdater->update($nameAttributeView)->shouldBeCalled();

        $resultView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name' => $nameAttributeView
                ]
            ]
        ];
        $this->getView()->shouldReturn($resultView);
    }

    function it_adds_multiple_product_values_children_in_the_same_group(
        ProductValueInterface $valueOne,
        AttributeInterface $attributeOne,
        ProductValueInterface $valueTwo,
        AttributeInterface $attributeTwo,
        AttributeGroup $group,
        FormView $valueFormView,
        $viewUpdaterRegistry
    ) {
        $valueOne->getAttribute()->willReturn($attributeOne);
        $valueOne->isRemovable()->willReturn(true);
        $valueOne->getLocale()->willReturn(null);
        $valueOne->getEntity()->willReturn(null);
        $attributeOne->getGroup()->willReturn($group);
        $attributeOne->getId()->willReturn(42);
        $attributeOne->getCode()->willReturn('name');
        $attributeOne->getLabel()->willReturn('Name');
        $attributeOne->getSortOrder()->willReturn(10);
        $attributeOne->getAttributeType()->willReturn('pim_catalog_text');
        $attributeOne->isLocalizable()->willReturn(false);
        $attributeOne->isScopable()->willReturn(false);

        $valueTwo->getAttribute()->willReturn($attributeTwo);
        $valueTwo->isRemovable()->willReturn(true);
        $valueTwo->getLocale()->willReturn(null);
        $valueTwo->getEntity()->willReturn(null);
        $attributeTwo->getGroup()->willReturn($group);
        $attributeTwo->getId()->willReturn(47);
        $attributeTwo->getCode()->willReturn('description');
        $attributeTwo->getLabel()->willReturn('Description');
        $attributeTwo->getSortOrder()->willReturn(15);
        $attributeTwo->getAttributeType()->willReturn('pim_catalog_text');
        $attributeTwo->isLocalizable()->willReturn(false);
        $attributeTwo->isScopable()->willReturn(false);

        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('general');
        $group->getLabel()->willReturn('General');

        $this->addChildren($valueOne, $valueFormView);
        $this->addChildren($valueTwo, $valueFormView);

        $viewUpdaterRegistry->getUpdaters()->willReturn([]);

        $resultView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name' => [
                        'id'                 => 42,
                        'isRemovable'        => true,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 10,
                        'allowValueCreation' => false,
                        'locale'             => null,
                        'value'              => $valueFormView,
                    ],
                    'description' => [
                        'id'                 => 47,
                        'isRemovable'        => true,
                        'code'               => 'description',
                        'label'              => 'Description',
                        'sortOrder'          => 15,
                        'allowValueCreation' => false,
                        'locale'             => null,
                        'value'              => $valueFormView,
                    ],
                ]
            ]
        ];
        $this->getView()->shouldReturn($resultView);
    }
}

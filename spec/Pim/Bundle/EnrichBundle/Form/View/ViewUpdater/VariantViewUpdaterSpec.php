<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\VariantViewUpdater;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class VariantViewUpdaterSpec extends ObjectBehavior
{
    function let(PropertyAccessorInterface $propertyAccessor)
    {
        $this->beAnInstanceOf('spec\Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ConcreteVariantViewUpdater');
        $this->beConstructedWith($propertyAccessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\VariantViewUpdater');
    }

    function it_updates_the_given_view($propertyAccessor, FormView $nameFormView, AbstractProductValue $nameValue, AbstractProduct $mug, GroupInterface $mugGroup, GroupType $variantGroupType, ProductTemplateInterface $mugTemplate, AttributeInterface $nameAttribute)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willReturn($nameValue);

        $nameValue->getEntity()->willReturn($mug);

        $mug->getVariantGroup()->willReturn($mugGroup);
        $mugGroup->getType()->willReturn($variantGroupType);
        $variantGroupType->isVariant()->willReturn(true);

        $mugGroup->getProductTemplate()->willReturn($mugTemplate);

        $nameValue->getAttribute()->willReturn($nameAttribute);

        $mugTemplate->hasValueForAttribute($nameAttribute)->willReturn(true);

        $nameFormView->getIterator()->willReturn(new \ArrayIterator([]));

        $this->update($view);

        $this->setView($nameFormView);
        $this->getVars()->shouldReturn([
            'value' => null,
            'attr' => [],
            'from_variant' => $mugGroup,
            'disabled' => true,
            'read_only' => true,
        ]);
    }

    function it_throws_an_exception_if_no_value_view_are_setted()
    {
        $view = [
            'attr' => []
        ];

        $this->shouldThrow('\LogicException')->during('update', [$view]);
    }

    function it_throws_an_exception_if_no_product_value_are_setted($propertyAccessor, FormView $nameFormView)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willThrow('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException');

        $this->shouldThrow('\LogicException')->during('update', [$view]);
    }

    function it_doesnt_update_the_view_if_there_is_no_product_attached_to_the_view($propertyAccessor, FormView $nameFormView, AbstractProductValue $nameValue)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willReturn($nameValue);

        $nameValue->getEntity()->willReturn(null);

        $this->update($view);

        $this->setView($nameFormView);
        $this->getVars()->shouldReturn([
            'value' => null,
            'attr' => []
        ]);
    }

    function it_doesnt_update_the_view_if_there_is_no_variant_group_attached_to_the_product($propertyAccessor, FormView $nameFormView, AbstractProductValue $nameValue, AbstractProduct $mug, GroupInterface $mugGroup, GroupType $variantGroupType)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willReturn($nameValue);

        $nameValue->getEntity()->willReturn($mug);

        $mug->getVariantGroup()->willReturn(null);

        $this->update($view);

        $this->setView($nameFormView);
        $this->getVars()->shouldReturn([
            'value' => null,
            'attr' => []
        ]);
    }

    function it_doesnt_update_the_view_if_there_is_no_template_attached_to_the_group($propertyAccessor, FormView $nameFormView, AbstractProductValue $nameValue, AbstractProduct $mug, GroupInterface $mugGroup, GroupType $variantGroupType, AttributeInterface $nameAttribute)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willReturn($nameValue);

        $nameValue->getEntity()->willReturn($mug);

        $mug->getVariantGroup()->willReturn($mugGroup);
        $mugGroup->getType()->willReturn($variantGroupType);
        $variantGroupType->isVariant()->willReturn(true);

        $mugGroup->getProductTemplate()->willReturn(null);

        $this->update($view);

        $this->setView($nameFormView);
        $this->getVars()->shouldReturn([
            'value' => null,
            'attr' => []
        ]);
    }

    function it_doesnt_update_the_view_if_there_is_no_attribute_in_the_template($propertyAccessor, FormView $nameFormView, AbstractProductValue $nameValue, AbstractProduct $mug, GroupInterface $mugGroup, GroupType $variantGroupType, ProductTemplateInterface $mugTemplate, AttributeInterface $nameAttribute)
    {
        $view = [
            'value' => $nameFormView,
            'attr' => []
        ];

        $propertyAccessor->getValue($nameFormView, 'vars[value]')->willReturn($nameValue);

        $nameValue->getEntity()->willReturn($mug);

        $mug->getVariantGroup()->willReturn($mugGroup);
        $mugGroup->getType()->willReturn($variantGroupType);
        $variantGroupType->isVariant()->willReturn(true);

        $mugGroup->getProductTemplate()->willReturn($mugTemplate);

        $nameValue->getAttribute()->willReturn($nameAttribute);

        $mugTemplate->hasValueForAttribute($nameAttribute)->willReturn(false);

        $this->update($view);

        $this->setView($nameFormView);
        $this->getVars()->shouldReturn([
            'value' => null,
            'attr' => []
        ]);
    }
}

class ConcreteVariantViewUpdater extends VariantViewUpdater
{
    protected $view;

    public function setView($view)
    {
        $this->view = $view;
    }

    public function getVars()
    {
        return $this->view->vars;
    }
}

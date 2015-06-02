<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class PriceCollectionTypeSpec extends ObjectBehavior
{
    function let(
        ConstraintGuesserInterface $guesser,
        CurrencyManager $currencyManager,
        AttributeInterface $attribute,
        ProductValueInterface $value
    ) {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(
            AbstractAttributeType::BACKEND_TYPE_PRICE,
            'pim_enrich_price_collection',
            $guesser,
            $currencyManager
        );
    }

    function it_builds_attribute_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(7);
    }

    function it_prepares_value_form_name($attribute, $value)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_PRICE);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_PRICE);
    }

    function it_prepares_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('pim_enrich_price_collection');
    }

    function it_prepares_value_form_options($attribute, $value)
    {
        $attribute->getLabel()->willReturn('Random label');
        $attribute->isRequired()->willReturn(true);

        $this->prepareValueFormOptions($value)->shouldReturn([
            'label'           => 'Random label',
            'required'        => true,
            'auto_initialize' => false,
            'label_attr'      => ['truncate' => true],
            'type'            => 'pim_enrich_price',
            'allow_add'       => true,
            'allow_delete'    => false,
            'by_reference'    => false
        ]);
    }

    function it_prepares_value_form_constraints($guesser, $attribute, $value)
    {
        $guesser->supportAttribute($attribute)->willReturn(true);
        $guesser->guessConstraints($attribute)->willReturn('test');

        $this->prepareValueFormConstraints($value)->shouldReturn([
            'constraints' => 'test'
        ]);
    }

    function it_prepares_default_value_form_constraints($guesser, $attribute, $value)
    {
        $guesser->supportAttribute($attribute)->willReturn(false);

        $this->prepareValueFormConstraints($value)->shouldReturn([]);
    }

    function it_prepares_value_form_data($value)
    {
        $value->getData()->willReturn('A value');
        $this->prepareValueFormData($value)->shouldReturn('A value');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_price_collection');
    }
}

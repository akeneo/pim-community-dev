<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;

class MetricTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, MeasureManager $manager)
    {
        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_METRIC, 'pim_enrich_metric', $guesser, $manager);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, AbstractAttribute $size)
    {
        $this->buildAttributeFormTypes($factory, $size)->shouldHaveCount(11);
    }

    function it_prepares_the_product_value_form(AbstractProductValue $value, AbstractAttribute $size)
    {
        $value->getAttribute()->willReturn($size);
        $size->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_METRIC);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_METRIC);
    }

    function it_prepares_the_product_value_form_alias(AbstractProductValue $value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('pim_enrich_metric');
    }

    function it_prepares_the_product_value_form_options(AbstractProductValue $value, AbstractAttribute $size, $manager)
    {
        $size->getLabel()->willReturn('size');
        $size->isRequired()->willReturn(false);
        $manager->getUnitSymbolsForFamily('Weight')->willReturn('Kg');
        $value->getAttribute()->willReturn($size);
        $size->getMetricFamily()->willReturn('Weight');
        $size->getDefaultMetricUnit()->willReturn('KILOGRAM');
        $value->getAttribute()->willReturn($size);

        $this->prepareValueFormOptions($value)->shouldHaveCount(7);
    }

    function it_prepares_the_product_value_form_constraints(AbstractProductValue $value, AbstractAttribute $size, $guesser)
    {
        $value->getAttribute()->willReturn($size);
        $guesser->supportAttribute($size)->willReturn(true);
        $guesser->guessConstraints($size)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data(AbstractProductValue $value, AbstractAttribute $size)
    {
        $metric = new Metric();
        $value->getData()->willReturn($metric);
        $this->prepareValueFormData($value)->shouldReturn($metric);
    }
}

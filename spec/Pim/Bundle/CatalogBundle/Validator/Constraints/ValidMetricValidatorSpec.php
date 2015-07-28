<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidMetric;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidMetricValidatorSpec extends ObjectBehavior
{
    function let(PropertyAccessorInterface $accessor, ExecutionContextInterface $context)
    {
        $measures = [
            'measures_config' => [
                'Weight' => [
                    'units' => [
                        'kg' => ''
                    ]
                ]

            ]
        ];
        $this->beConstructedWith($accessor, $measures);
        $this->initialize($context);
    }

    function it_validates_metric_attribute(
        $accessor,
        $context,
        ValidMetric $constraint,
        AttributeInterface $attribute
    ) {
        $accessor->getValue($attribute, 'metricFamily')
            ->shouldBeCalled()
            ->willReturn('Weight');
        $accessor->getValue($attribute, 'defaultMetricUnit')
            ->shouldBeCalled()
            ->willReturn('kg');
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, $constraint)->shouldReturn(null);
    }

    function it_validates_product_metric(
        $accessor,
        $context,
        ValidMetric $constraint,
        MetricInterface $metric
    ) {
        $metric->getData()->willReturn(12);
        $accessor->getValue($metric, 'family')
            ->shouldBeCalled()
            ->willReturn('Weight');
        $accessor->getValue($metric, 'unit')
            ->shouldBeCalled()
            ->willReturn('kg');
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($metric, $constraint)->shouldReturn(null);
    }

    function it_validates_product_value_with_metric_data(
        $accessor,
        $context,
        ValidMetric $constraint,
        ProductValueInterface $value,
        MetricInterface $metric
    ) {
        $value->getMetric()->willReturn($metric);
        $metric->getUnit()->willReturn('cm');
        $metric->getData()->willReturn(12);
        $accessor->getValue($metric, 'family')
            ->shouldBeCalled()
            ->willReturn('Weight');
        $accessor->getValue($metric, 'unit')
            ->shouldBeCalled()
            ->willReturn('kg');
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_family_doesnt_exist(
        $accessor,
        $context,
        ValidMetric $constraint,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $accessor->getValue($attribute, 'metricFamily')
            ->shouldBeCalled()
            ->willReturn('UnexistingFamily');
        $accessor->getValue($attribute, 'defaultMetricUnit')
            ->shouldBeCalled()
            ->willReturn('kg');

        $context->buildViolation(Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('metricFamily')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_when_unit_is_not_consistent_with_family(
        $accessor,
        $context,
        ValidMetric $constraint,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $accessor->getValue($attribute, 'metricFamily')
            ->shouldBeCalled()
            ->willReturn('Weight');
        $accessor->getValue($attribute, 'defaultMetricUnit')
            ->shouldBeCalled()
            ->willReturn('InconsistentUnit');

        $context->buildViolation(Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('defaultMetricUnit')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint)->shouldReturn(null);
    }
}

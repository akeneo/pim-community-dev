<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;

class ConstraintViolationNormalizerSpec extends ObjectBehavior
{
    function it_supports_constraint_violation(ConstraintViolation $violation)
    {
        $this->supportsNormalization($violation, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_a_property_violation(ConstraintViolation $violation) {
        $violation->getPropertyPath()->willReturn('metricFamily');
        $violation->getMessage()->willReturn('Please specify a valid metric family');
        $constraint = new ValidMetric();
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api')->shouldReturn([
            'path'    => 'metric_family',
            'message' => 'Please specify a valid metric family',
            'global'  => false,
        ]);
    }

    function it_normalizes_a_violation_with_explicit_path(ConstraintViolation $violation) {
        $violation->getPropertyPath()->willReturn(null);
        $violation->getMessage()->willReturn('The max date must be greater than the min date.');
        $constraint = new ValidDateRange();
        $constraint->payload['standardPropertyPath'] = 'dateMin';
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api')->shouldReturn([
            'message' => 'The max date must be greater than the min date.',
            'global'  => true,
        ]);
    }

    function it_normalizes_global_violation(ConstraintViolation $violation) {
        $violation->getPropertyPath()->willReturn(null);
        $violation->getMessage()->willReturn('The max date must be greater than the min date.');
        $constraint = new ValidDateRange();
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api')->shouldReturn([
            'message' => 'The max date must be greater than the min date.',
            'global'  => true,
        ]);
    }

    public function it_normalizes_violation_without_translating(
        ConstraintViolation $violation,
        ValidDateRange $constraint
    ) {
        $violation->getPropertyPath()->willReturn('foo');
        $violation->getMessage()->willReturn('The max date must be greater than the min date.');
        $violation->getMessageTemplate()->willReturn('attribute_date_must_be_greater');
        $violation->getParameters()->willReturn([]);
        $violation->getInvalidValue()->willReturn('');
        $violation->getConstraint()->willReturn($constraint);
        $violation->getPlural()->willReturn(null);

        $this->normalize($violation, 'internal_api', ['translate' => false])->shouldReturn([
            'messageTemplate' => 'attribute_date_must_be_greater',
            'parameters' => [],
            'message' => 'The max date must be greater than the min date.',
            'propertyPath' => 'foo',
            'invalidValue' => '',
            'plural' => null
        ]);
    }
}

<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Validator\Constraints\SingleIdentifierAttribute;
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
        $violation->getMessage()->willReturn('An identifier attribute already exists.');
        $constraint = new SingleIdentifierAttribute();
        $constraint->payload['standardPropertyPath'] = 'type';
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api')->shouldReturn([
            'message' => 'An identifier attribute already exists.',
            'global'  => true,
        ]);
    }

    function it_normalizes_global_violation(ConstraintViolation $violation) {
        $violation->getPropertyPath()->willReturn(null);
        $violation->getMessage()->willReturn('An identifier attribute already exists.');
        $constraint = new SingleIdentifierAttribute();
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api')->shouldReturn([
            'message' => 'An identifier attribute already exists.',
            'global'  => true,
        ]);
    }

    public function it_normalizes_violation_without_translating(
        ConstraintViolation $violation,
        SingleIdentifierAttribute $constraint
    ) {
        $violation->getPropertyPath()->willReturn('foo');
        $violation->getMessage()->willReturn('An identifier attribute already exists.');
        $violation->getMessageTemplate()->willReturn('identifier_attribute_already_exists');
        $violation->getParameters()->willReturn([]);
        $violation->getInvalidValue()->willReturn('');
        $violation->getConstraint()->willReturn($constraint);

        $this->normalize($violation, 'internal_api', ['translate' => false])->shouldReturn([
            'messageTemplate' => 'identifier_attribute_already_exists',
            'parameters' => [],
            'message' => 'An identifier attribute already exists.',
            'propertyPath' => 'foo',
            'invalidValue' => '',
        ]);
    }
}

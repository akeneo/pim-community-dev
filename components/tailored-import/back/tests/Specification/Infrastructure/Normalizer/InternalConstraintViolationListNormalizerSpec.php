<?php

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Normalizer;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class InternalConstraintViolationListNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_a_constraint_violation_list(): void
    {
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('an_error', 'message_template', ['{{ param_1 }}' => 'value_1'], '', '[file]', null),
            new ConstraintViolation('another_error', 'message_template', ['{{ param_1 }}' => 'value_1'], '', '[file]', null),
        ]);

        $expectedNormalizedConstraintViolationList = [
            [
                'propertyPath' => '[file]',
                'message' => 'an_error',
                'messageTemplate' => 'message_template',
                'parameters' => ['{{ param_1 }}' => 'value_1'],
            ],
            [
                'propertyPath' => '[file]',
                'message' => 'another_error',
                'messageTemplate' => 'message_template',
                'parameters' => ['{{ param_1 }}' => 'value_1'],
            ],
        ];

        $this->normalize($constraintViolationList)->shouldReturn($expectedNormalizedConstraintViolationList);
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Normalizer;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListNormalizerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    public function it_supports_normalization_of_constraint_violation_list(
        ConstraintViolationList $constraintViolationList
    ) {
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
        $this->supportsNormalization($constraintViolationList)->shouldReturn(true);
    }

    public function it_normalizes_a_constraint_violation_list(
        NormalizerInterface $normalizer,
        ConstraintViolationInterface $constraintViolationA,
        ConstraintViolationInterface $constraintViolationB
    ) {
        $constraintViolationA->getMessageTemplate()->willReturn('the_message_template_a');
        $constraintViolationA->getParameters()->willReturn(['parameters_a']);
        $constraintViolationA->getMessage()->willReturn('The message A');
        $constraintViolationA->getPropertyPath()->willReturn('property.path.a');
        $constraintViolationA->getInvalidValue()->willReturn('toto');

        $constraintViolationB->getMessageTemplate()->willReturn('the_message_template_b');
        $constraintViolationB->getParameters()->willReturn(['parameters_b']);
        $constraintViolationB->getMessage()->willReturn('The message B');
        $constraintViolationB->getPropertyPath()->willReturn('property.path.b');
        $constraintViolationB->getInvalidValue()->willReturn('tata');

        $normalizer->normalize('toto')->willReturn('toto_normalized');
        $normalizer->normalize('tata')->willReturn('tata_normalized');

        $constraintViolationList = [$constraintViolationA, $constraintViolationB];

        $this->normalize($constraintViolationList)->shouldReturn(
            [
                [
                    'messageTemplate' => 'the_message_template_a',
                    'parameters' => ['parameters_a'],
                    'message' => 'The message A',
                    'propertyPath' => 'property.path.a',
                    'invalidValue' => 'toto_normalized',
                ],
                [
                    'messageTemplate' => 'the_message_template_b',
                    'parameters' => ['parameters_b'],
                    'message' => 'The message B',
                    'propertyPath' => 'property.path.b',
                    'invalidValue' => 'tata_normalized',
                ],
            ]
        );

    }
}

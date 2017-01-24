<?php

namespace spec\Pim\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationNormalizerSpec extends ObjectBehavior
{
    function it_normalizes_an_exception()
    {
        $violationCode = new ConstraintViolation('Not Blank', '', [], '', 'code', '');
        $violationName = new ConstraintViolation('Too long', '', [], '', 'name', '');
        $constraintViolation = new ConstraintViolationList([$violationCode, $violationName]);
        $exception = new ViolationHttpException($constraintViolation);

        $this->normalize($exception)->shouldReturn([
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Validation failed.',
            'errors'  => [
                ['field' => 'code', 'message' => 'Not Blank'],
                ['field' => 'name', 'message' => 'Too long']
            ]
        ]);
    }
}

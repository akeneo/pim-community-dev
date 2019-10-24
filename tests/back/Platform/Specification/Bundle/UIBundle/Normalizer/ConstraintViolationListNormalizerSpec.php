<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListNormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $normalizer = new OwnConstraintNormalizer();
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_violations()
    {
        $constraintA = new ConstraintViolation('constraint A', null, [], null, null, null);
        $constraintB = new ConstraintViolation('constraint B', null, [], null, null, null);
        $constraints = new ConstraintViolationList([$constraintA, $constraintB]);

        $this->normalize($constraints)->shouldReturn(['constraint A', 'constraint B']);
    }

    function it_supports_only_constraint_list()
    {
        $this->supportsNormalization(new stdClass())->shouldReturn(false);
        $this->supportsNormalization(new ConstraintViolationList([]))->shouldReturn(true);
    }
}

class OwnConstraintNormalizer implements NormalizerInterface
{
    public function normalize($constraint, $format = null, array $context = [])
    {
        return $constraint->getMessage();
    }

    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}

<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Normalizer\GroupNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(GroupNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_group()
    {
        $group = new Group('Administrators');
        $format = 'standard';

        $this->supportsNormalization($group, $format)->shouldBe(true);
        $this->normalize($group)->shouldBe(['name' => 'Administrators']);
    }

    function it_cannot_normalize_a_non_group_class_instance()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('normalize', [new \StdClass()]);
    }

    function it_cannot_normalize_a_group_with_an_unknown_format()
    {
        $group = new Group('Administrators');
        $format = 'unknown';

        $this->supportsNormalization($group, $format)->shouldBe(false);
    }
}

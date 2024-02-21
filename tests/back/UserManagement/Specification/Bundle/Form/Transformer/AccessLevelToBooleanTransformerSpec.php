<?php

namespace Specification\Akeneo\UserManagement\Bundle\Form\Transformer;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\DataTransformerInterface;

class AccessLevelToBooleanTransformerSpec extends ObjectBehavior
{
    function it_is_a_form_data_transformer()
    {
        $this->shouldImplement(DataTransformerInterface::class);
    }

    function it_transforms_access_level_to_a_boolean()
    {
        $this->transform(AccessLevel::SYSTEM_LEVEL)->shouldReturn(true);
        $this->transform(AccessLevel::NONE_LEVEL)->shouldReturn(false);
    }

    function it_reverse_transforms_boolean_to_access_level()
    {
        $this->reverseTransform(true)->shouldReturn(AccessLevel::SYSTEM_LEVEL);
        $this->reverseTransform(false)->shouldReturn(AccessLevel::NONE_LEVEL);
    }

    function it_transforms_null_value_to_en_empty_string()
    {
        $this->transform(null)->shouldReturn('');
    }

    function it_transforms_invalid_access_level_to_false()
    {
        $this->transform(AccessLevel::LOCAL_LEVEL)->shouldReturn(false);
        $this->transform('foo')->shouldReturn(false);
        $this->transform([])->shouldReturn(false);
    }

    function it_reverse_transforms_invalid_value_to_none_access_level()
    {
        $this->reverseTransform(1)->shouldReturn(AccessLevel::NONE_LEVEL);
        $this->reverseTransform('bar')->shouldReturn(AccessLevel::NONE_LEVEL);
        $this->reverseTransform('5')->shouldReturn(AccessLevel::NONE_LEVEL);
    }
}

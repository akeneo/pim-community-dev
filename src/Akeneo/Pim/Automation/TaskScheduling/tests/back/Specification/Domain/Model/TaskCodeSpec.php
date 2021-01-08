<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TaskCodeSpec extends ObjectBehavior
{
    function it_can_be_created_with_a_valid_code()
    {
        $this->beConstructedThrough('fromString', ['valid_code']);
        $this->shouldBeAnInstanceOf(TaskCode::class);
        $this->asString()->shouldBe('valid_code');
    }

    function it_cannot_be_created_with_an_empty_code()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(new \InvalidArgumentException('Task code should be a non empty string'))
            ->duringInstantiation();
    }

    function it_cannot_be_created_with_forbidden_character()
    {
        $this->beConstructedThrough('fromString', ['non valid']);
        $this->shouldThrow(new \InvalidArgumentException('Task code may contain only letters, numbers and underscores. "non valid" given'))
            ->duringInstantiation();
    }

    function it_cannot_be_created_with_too_long_string()
    {
        $this->beConstructedThrough('fromString', [str_repeat('a', 260)]);
        $this->shouldThrow(new \InvalidArgumentException('Task code cannot be longer than 255 characters'))
            ->duringInstantiation();
    }

    function it_can_compare_itself_to_another_code()
    {
        $this->beConstructedThrough('fromString', ['valid_code']);
        $this->equals(TaskCode::fromString('valid_code'))->shouldBe(true);
        $this->equals(TaskCode::fromString('another_valid_code'))->shouldBe(false);
    }
}

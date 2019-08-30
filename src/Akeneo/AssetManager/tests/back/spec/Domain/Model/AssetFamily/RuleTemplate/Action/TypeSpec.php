<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Type;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TypeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromNormalized', ['add']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Type::class);
    }

    public function it_cannot_be_created_from_an_empty_string()
    {
        $this->beConstructedThrough('createFromNormalized', ['']);
        $this->shouldThrow(new \InvalidArgumentException('Type value of action should not be empty'))->duringInstantiation();
    }

    public function it_can_get_the_string_value()
    {
        $this->stringValue()->shouldReturn('add');
    }
}

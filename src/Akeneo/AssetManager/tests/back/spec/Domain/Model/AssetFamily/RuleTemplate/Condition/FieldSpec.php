<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition\Field;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FieldSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromNormalized', ['sku']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Field::class);
    }

    public function it_cannot_be_created_from_an_empty_string()
    {
        $this->beConstructedThrough('createFromNormalized', ['']);
        $this->shouldThrow(new \InvalidArgumentException('Field value of condition should not be empty'))->duringInstantiation();
    }

    public function it_can_get_the_string_value()
    {
        $this->stringValue()->shouldReturn('sku');
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition\Value;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromNormalized', ['{{product_code}}']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Value::class);
    }
    
    public function it_can_get_the_string_value()
    {
        $this->stringValue()->shouldReturn('{{product_code}}');
    }
}

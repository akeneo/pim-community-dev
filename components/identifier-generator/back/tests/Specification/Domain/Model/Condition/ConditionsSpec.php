<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConditionsSpec extends ObjectBehavior
{
    public function it_cannot_be_instantiated_with_something_else_than_a_condition(): void
    {
        $this->beConstructedThrough('fromArray', [[new \stdClass()]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_with_wrong_type(): void
    {
        $this->beConstructedThrough('fromNormalized', [[['type' => 'unknown']]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_with_empty_type(): void
    {
        $this->beConstructedThrough('fromNormalized', [[['value' => true]]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_instantiated_with_valid_normalized_value(): void
    {
        $this->beConstructedThrough('fromNormalized', [[['type' => 'enabled', 'value' => true]]]);
        $this->shouldNotThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_normalized(): void
    {
        $this->beConstructedThrough('fromArray', [[new Enabled(true)]]);
        $this->normalize()->shouldReturn([
            ['type' => 'enabled', 'value' => true],
        ]);
    }

    public function it_should_return_conjunction(): void
    {
        $condition1 = new Enabled(true);
        $condition2 = Family::fromNormalized(['type' => 'family', 'operator' => 'EMPTY']);
        $this->beConstructedThrough('fromArray', [[$condition1]]);
        $this->and([$condition2])->shouldBeLike(Conditions::fromArray([$condition1, $condition2]));
    }
}

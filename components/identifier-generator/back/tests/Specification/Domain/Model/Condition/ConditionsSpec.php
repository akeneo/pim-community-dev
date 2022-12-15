<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
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

    public function it_matches_if_condition_matches(): void
    {
        $this->beConstructedThrough('fromArray', [[new Enabled(true)]]);
        $this->match(new ProductProjection('', true, ''))->shouldReturn(true);
    }

    public function it_does_not_match_if_condition_doest_not_match(): void
    {
        $this->beConstructedThrough('fromArray', [[new Enabled(true)]]);
        $this->match(new ProductProjection('', false, ''))->shouldReturn(false);
    }
}

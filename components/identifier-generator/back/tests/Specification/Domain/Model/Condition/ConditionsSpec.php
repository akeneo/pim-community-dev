<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

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
}

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
    function it_cannot_be_instantiate_with_not_conditions_interface()
    {
        $this->beConstructedThrough('fromArray', [[new \stdClass()]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}

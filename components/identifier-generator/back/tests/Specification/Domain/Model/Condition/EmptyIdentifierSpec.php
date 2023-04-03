<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('sku');
    }

    public function it_is_a_condition()
    {
        $this->shouldBeAnInstanceOf(EmptyIdentifier::class);
        $this->shouldImplement(ConditionInterface::class);
    }
}

<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_is_a_family(): void
    {
        $this->shouldImplement(ConditionInterface::class);
        $this->shouldBeAnInstanceOf(Family::class);
    }

    public function it_should_normalize(): void
    {
        $this->fromNormalized([]);
    }
}

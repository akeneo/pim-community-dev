<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\OperationList;
use PhpSpec\ObjectBehavior;

class OperationListSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('fromNormalized', [[['operator' => Operation::MULTIPLY, 'field' => 'width']]]);
        $this->shouldHaveType(OperationList::class);
        $this->shouldImplement(\IteratorAggregate::class);
    }

    function it_holds_at_least_one_operation()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(new \InvalidArgumentException('The operation list expects at least one operation'))
            ->duringInstantiation();
    }

    function it_holds_a_list_of_operations()
    {
        $this->beConstructedThrough('fromNormalized', [[
            ['operator' => Operation::MULTIPLY, 'value' => 3.1415927],
            ['operator' => Operation::MULTIPLY, 'value' => 2],
        ]]);

        foreach ($this->getIterator() as $operation) {
            $operation->shouldBeAnInstanceOf(Operation::class);
        }
    }
}

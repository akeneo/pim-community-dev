<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Presenter;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Presenter\TablePresenter;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;

class TablePresenterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(PresenterInterface::class);
        $this->shouldHaveType(TablePresenter::class);
    }

    function it_presents_changes()
    {
        $before = [
            ['quantity' => 10, 'ingredient' => 'sugar'],
            ['quantity' => 30, 'ingredient' => 'salt'],
        ];
        $after = [
            ['quantity' => 20, 'ingredient' => 'sugar'],
            ['quantity' => 30, 'ingredient' => 'eggs'],
        ];
        $this->present(Table::fromNormalized($before), [
            'data' => $after,
            'attribute' => 'nutrition'
        ])->shouldReturn([
            'before' => $before,
            'after' => $after,
            'attributeCode' => 'nutrition',
        ]);
    }

    function it_presents_empty_values()
    {
        $this->present(new \stdClass(), ['data' => [], 'attribute' => 'nutrition'])->shouldReturn([
            'before' => [],
            'after' => [],
            'attributeCode' => 'nutrition',
        ]);
    }
}

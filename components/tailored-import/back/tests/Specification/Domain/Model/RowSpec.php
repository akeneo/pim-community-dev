<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    public function it_returns_the_cell_data_at_column()
    {
        $this->beConstructedWith([
            'c8f9f8e7-f8f8-4f8e-ac42-cca126cc08de' => 'My sku',
            'a07b9dd7-f0ff-4d89-85a5-dee411cf53c2' => 'My description',
            '5717b3de-4dfb-4f8e-ac42-cca126cc08de' => 'My name',
        ]);

        $this->getCellData('c8f9f8e7-f8f8-4f8e-ac42-cca126cc08de')->shouldBeLike(new StringValue('My sku'));
        $this->getCellData('a07b9dd7-f0ff-4d89-85a5-dee411cf53c2')->shouldBeLike(new StringValue('My description'));
        $this->getCellData('5717b3de-4dfb-4f8e-ac42-cca126cc08de')->shouldBeLike(new StringValue('My name'));
    }

    public function it_throws_an_exception_when_cell_uuid_is_invalid()
    {
        $this->beConstructedWith(['invalid-uuid' => 'My sku']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_cell_value_is_invalid()
    {
        $this->beConstructedWith(['c8f9f8e7-f8f8-4f8e-ac42-cca126cc08de' => 1]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}

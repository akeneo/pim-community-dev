<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\Operation;

use PhpSpec\ObjectBehavior;

class ReplacementOperationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'a_value' => 'a_replacement_value',
            'another_value' => 'another_replacement_value',
        ]);
    }

    public function it_returns_the_mapping()
    {
        $this->getMapping()->shouldReturn([
            'a_value' => 'a_replacement_value',
            'another_value' => 'another_replacement_value',
        ]);
    }

    public function it_returns_true_if_a_value_is_mapped()
    {
        $this->hasMappedValue('another_value')->shouldReturn(true);
        $this->hasMappedValue('Another_Value')->shouldReturn(true);
    }

    public function it_returns_false_when_it_check_if_a_value_is_not_mapped()
    {
        $this->hasMappedValue('not_mapped_value')->shouldReturn(false);
    }

    public function it_returns_a_mapped_value()
    {
        $this->getMappedValue('another_value')->shouldReturn('another_replacement_value');
        $this->getMappedValue('AnotheR_value')->shouldReturn('another_replacement_value');
    }

    public function it_returns_null_if_value_is_not_mapped()
    {
        $this->getMappedValue('not_mapped_value')->shouldReturn(null);
    }
}

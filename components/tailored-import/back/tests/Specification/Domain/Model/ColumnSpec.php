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

use PhpSpec\ObjectBehavior;

class ColumnSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'index' => 0,
            'label' => 'label',
            'uuid' => 'f9be9837-df82-4ad7-8c76-565ac274e900',
        ]]);

        $this->getIndex()->shouldReturn(0);
        $this->getLabel()->shouldReturn('label');
        $this->getUuid()->shouldReturn('f9be9837-df82-4ad7-8c76-565ac274e900');
    }

    public function it_throws_an_exception_when_uuid_is_invalid()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'index' => 0,
            'label' => 'label',
            'uuid' => 'invalid_uuid',
        ]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_index_is_invalid()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'index' => -1,
            'label' => 'label',
            'uuid' => 'f9be9837-df82-4ad7-8c76-565ac274e900',
        ]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_label_is_invalid()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'index' => 0,
            'label' => '',
            'uuid' => 'f9be9837-df82-4ad7-8c76-565ac274e900',
        ]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}

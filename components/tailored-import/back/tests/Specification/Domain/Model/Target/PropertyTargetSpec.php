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

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Target;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use PhpSpec\ObjectBehavior;

class PropertyTargetSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('create', [
            'test_code',
            'set',
            'skip',
        ]);

        $this->getCode()->shouldReturn('test_code');
        $this->getActionIfNotEmpty()->shouldReturn('set');
        $this->getActionIfEmpty()->shouldReturn('skip');
    }

    public function it_implements_target_interface()
    {
        $this->shouldBeAnInstanceOf(TargetInterface::class);
    }
}

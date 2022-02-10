<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Application\Common;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetPropertySpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'code' => 'test_code',
            'action' => 'set',
            'if_empty' => 'test'
        ]]);

        $this->code()->shouldReturn('test_code');
        $this->action()->shouldReturn('set');
        $this->ifEmpty()->shouldReturn('test');
    }
}
<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetAttributeSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'code' => 'test_code',
            'channel' => 'web',
            'locale' => 'fr_FR',
            'action' => 'set',
            'if_empty' => 'skip',
        ]]);

        $this->getCode()->shouldReturn('test_code');
        $this->getChannel()->shouldReturn('web');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getActionIfNotEmpty()->shouldReturn('set');
        $this->getActionIfEmpty()->shouldReturn('skip');
    }
}

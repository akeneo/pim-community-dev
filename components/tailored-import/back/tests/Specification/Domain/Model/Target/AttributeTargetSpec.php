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

class AttributeTargetSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('create', [
            'test_code',
            'pim_catalog_text',
            'web',
            'fr_FR',
            'set',
            'skip',
            null,
        ]);

        $this->getCode()->shouldReturn('test_code');
        $this->getAttributeType()->shouldReturn('pim_catalog_text');
        $this->getChannel()->shouldReturn('web');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getActionIfNotEmpty()->shouldReturn('set');
        $this->getActionIfEmpty()->shouldReturn('skip');
        $this->getSourceConfiguration()->shouldReturn(null);
    }

    public function it_implements_target_interface()
    {
        $this->shouldBeAnInstanceOf(TargetInterface::class);
    }
}

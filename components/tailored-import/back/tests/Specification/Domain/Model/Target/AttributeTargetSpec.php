<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Target;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        $this->getType()->shouldReturn('pim_catalog_text');
        $this->getChannel()->shouldReturn('web');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getActionIfNotEmpty()->shouldReturn('set');
        $this->getActionIfEmpty()->shouldReturn('skip');
        $this->getSourceParameter()->shouldReturn(null);
    }
}

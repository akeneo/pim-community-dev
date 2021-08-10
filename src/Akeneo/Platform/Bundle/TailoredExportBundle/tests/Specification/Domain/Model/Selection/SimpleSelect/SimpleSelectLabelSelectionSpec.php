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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\Selection\SimpleSelect;

use PhpSpec\ObjectBehavior;

class SimpleSelectLabelSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'fr_FR',
            'an_attribute_code'
        );
    }

    public function it_returns_the_locale()
    {
        $this->getLocale()->shouldReturn('fr_FR');
    }

    public function it_returns_the_attribute_code()
    {
        $this->getAttributeCode()->shouldReturn('an_attribute_code');
    }
}

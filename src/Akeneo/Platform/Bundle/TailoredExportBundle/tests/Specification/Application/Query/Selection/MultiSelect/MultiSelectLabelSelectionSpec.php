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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect;

use PhpSpec\ObjectBehavior;

class MultiSelectLabelSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('/', 'en_US', 'an_attribute_code');
    }

    public function it_returns_the_separator()
    {
        $this->getSeparator()->shouldReturn('/');
    }

    public function it_returns_the_locale()
    {
        $this->getLocale()->shouldReturn('en_US');
    }

    public function it_returns_the_attribute_code()
    {
        $this->getAttirbuteCode()->shouldReturn('an_attribute_code');
    }
}

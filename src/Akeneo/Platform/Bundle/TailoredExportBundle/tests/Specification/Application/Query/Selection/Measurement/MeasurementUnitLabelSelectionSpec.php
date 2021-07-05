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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement;

use PhpSpec\ObjectBehavior;

class MeasurementUnitLabelSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('weight', 'en_US');
    }

    public function it_returns_the_metric_family()
    {
        $this->getSeparator()->shouldReturn('weight');
    }

    public function it_returns_the_locale()
    {
        $this->getLocale()->shouldReturn('en_US');
    }
}

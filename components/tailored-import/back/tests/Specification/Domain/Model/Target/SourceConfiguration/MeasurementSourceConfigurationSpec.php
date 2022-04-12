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

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\MeasurementSourceConfiguration;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;
use PhpSpec\ObjectBehavior;

class MeasurementSourceConfigurationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('METER', ',');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementSourceConfiguration::class);
    }

    public function it_implements_source_configuration_interface()
    {
        $this->shouldBeAnInstanceOf(SourceConfigurationInterface::class);
    }

    public function it_returns_unit()
    {
        $this->getUnit()->shouldReturn('METER');
    }

    public function it_returns_decimal_separator()
    {
        $this->getDecimalSeparator()->shouldReturn(',');
    }
}

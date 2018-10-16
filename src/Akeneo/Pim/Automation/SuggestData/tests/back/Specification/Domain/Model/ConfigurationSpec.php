<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['token' => 'value']);
    }

    public function it_is_configuration(): void
    {
        $this->shouldHaveType(Configuration::class);
    }

    public function it_gets_the_values(): void
    {
        $this->getValues()->shouldReturn(['token' => 'value']);
    }

    public function it_gets_the_token(): void
    {
        $this->getToken()->shouldReturn('value');
    }

    public function it_sets_new_values(): void
    {
        $this->setValues(['new_field' => 'new_value']);

        $this->getValues()->shouldReturn(['new_field' => 'new_value']);
    }

    public function it_is_normalizable(): void
    {
        $this->normalize()->shouldReturn([
            'code' => Configuration::PIM_AI_CODE,
            'values' => ['token' => 'value'],
        ]);
    }
}

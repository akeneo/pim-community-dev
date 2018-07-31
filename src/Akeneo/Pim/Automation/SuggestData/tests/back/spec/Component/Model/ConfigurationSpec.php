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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith('foobar', ['token' => 'value']);
    }

    function it_is_configuration()
    {
        $this->shouldHaveType(Configuration::class);
    }

    function it_gets_the_configuration_code()
    {
        $this->getCode()->shouldReturn('foobar');
    }

    function it_gets_the_values()
    {
        $this->getValues()->shouldReturn(['token' => 'value']);
    }

    function it_gets_the_token()
    {
        $this->getToken()->shouldReturn('value');
    }

    function it_sets_new_values()
    {
        $this->setValues(['new_field' => 'new_value']);

        $this->getValues()->shouldReturn(['new_field' => 'new_value']);
    }

    function it_is_normalizable()
    {
        $this->normalize()->shouldReturn([
            'code' => 'foobar',
            'values' => ['token' => 'value'],
        ]);
    }
}

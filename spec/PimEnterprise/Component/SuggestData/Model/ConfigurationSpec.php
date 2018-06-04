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

namespace spec\PimEnterprise\Component\SuggestData\Model;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Model\Configuration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith('foobar', ['field' => 'value']);
    }

    function it_is_configuration()
    {
        $this->shouldHaveType(Configuration::class);
    }

    function it_gets_the_configuration_code()
    {
        $this->getCode()->shouldReturn('foobar');
    }

    function it_gets_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn(['field' => 'value']);
    }

    function it_sets_new_configuration_fields()
    {
        $this->setConfigurationFields(['new_field' => 'new_value']);

        $this->getConfigurationFields()->shouldReturn(['new_field' => 'new_value']);
    }

    function it_is_normalizable()
    {
        $this->normalize()->shouldReturn([
            'code' => 'foobar',
            'configuration_fields' => ['field' => 'value'],
        ]);
    }
}

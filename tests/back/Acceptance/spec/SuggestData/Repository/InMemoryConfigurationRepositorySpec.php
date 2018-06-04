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

namespace spec\AkeneoEnterprise\Test\Acceptance\SuggestData\Repository;

use AkeneoEnterprise\Test\Acceptance\SuggestData\Repository\InMemoryConfigurationRepository;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryConfigurationRepositorySpec extends ObjectBehavior
{
    function it_is_an_in_memory_configuration_repository()
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType(InMemoryConfigurationRepository::class);
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }

    function it_finds_a_configuration_by_its_code()
    {
        $configuration = new Configuration('foobar', ['field' => 'value']);
        $this->beConstructedWith([$configuration]);

        $this->find('foobar')->shouldReturn($configuration);
    }

    function it_finds_no_configuration_if_there_is_no_configuration_for_the_provided_code()
    {
        $configuration = new Configuration('foobar', ['field' => 'value']);
        $this->beConstructedWith([$configuration]);

        $this->find('another_code')->shouldReturn(null);
    }

    function it_saves_a_configuration()
    {
        $configuration = new Configuration('foobar', ['field' => 'value']);
        $this->beConstructedWith([]);

        $this->save($configuration);

        $this->find('foobar')->shouldReturn($configuration);
    }
}

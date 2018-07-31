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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderRegistry;
use PhpSpec\ObjectBehavior;

class DataProviderRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DataProviderRegistry::class);
    }

    function it_registers_a_data_provider(DataProviderInterface $inMemoryAdapter)
    {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
    }

    function it_returns_defined_adapter(DataProviderInterface $inMemoryAdapter)
    {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->getDataProvider('in_memory')->shouldReturn($inMemoryAdapter);
    }

    function it_returns_the_right_data_provider(
        DataProviderInterface $inMemoryAdapter,
        DataProviderInterface $pimAiAdapter
    ) {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->addDataProvider('pim_ai', $pimAiAdapter)->shouldReturn(null);
        $this->getDataProvider('in_memory')->shouldReturn($inMemoryAdapter);
        $this->getDataProvider('pim_ai')->shouldReturn($pimAiAdapter);
    }

    function it_throws_an_exception_when_the_data_provider_is_not_registered(
        DataProviderInterface $inMemoryAdapter
    ) {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this
            ->shouldThrow(new \Exception('Data provider "unexisting_data_provider" not found'))
            ->during('getDataProvider', ['unexisting_data_provider']);
    }
}

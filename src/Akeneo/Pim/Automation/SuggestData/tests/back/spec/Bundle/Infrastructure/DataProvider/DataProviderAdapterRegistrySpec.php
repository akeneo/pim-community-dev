<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderAdapterInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\DataProviderAdapterRegistry;
use PhpSpec\ObjectBehavior;

class DataProviderAdapterRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DataProviderAdapterRegistry::class);
    }

    function it_registers_a_data_provider_adapter(DataProviderAdapterInterface $inMemoryAdapter)
    {
        $this->addAdapter('in_memory', $inMemoryAdapter)->shouldReturn(null);
    }

    function it_returns_defined_adapter(DataProviderAdapterInterface $inMemoryAdapter)
    {
        $this->addAdapter('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->getAdapter('in_memory')->shouldReturn($inMemoryAdapter);
    }

    function it_returns_the_right_adapter(
        DataProviderAdapterInterface $inMemoryAdapter,
        DataProviderAdapterInterface $pimAiAdapter
    ) {
        $this->addAdapter('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->addAdapter('pim_ai', $pimAiAdapter)->shouldReturn(null);
        $this->getAdapter('in_memory')->shouldReturn($inMemoryAdapter);
        $this->getAdapter('pim_ai')->shouldReturn($pimAiAdapter);
    }

    function it_throws_an_exception_when_the_adapter_is_not_registered(
        DataProviderAdapterInterface $inMemoryAdapter
    ) {
        $this->addAdapter('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this
            ->shouldThrow(new \Exception('Adapter "unexisting_adapter" not found'))
            ->during('getAdapter', ['unexisting_adapter']);
    }
}

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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderRegistry;
use PhpSpec\ObjectBehavior;

class DataProviderRegistrySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DataProviderRegistry::class);
    }

    public function it_registers_a_data_provider(DataProviderInterface $inMemoryAdapter): void
    {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
    }

    public function it_returns_defined_adapter(DataProviderInterface $inMemoryAdapter): void
    {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->getDataProvider('in_memory')->shouldReturn($inMemoryAdapter);
    }

    public function it_returns_the_right_data_provider(
        DataProviderInterface $inMemoryAdapter,
        DataProviderInterface $franklinAdapter
    ): void {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this->addDataProvider('franklin', $franklinAdapter)->shouldReturn(null);
        $this->getDataProvider('in_memory')->shouldReturn($inMemoryAdapter);
        $this->getDataProvider('franklin')->shouldReturn($franklinAdapter);
    }

    public function it_throws_an_exception_when_the_data_provider_is_not_registered(
        DataProviderInterface $inMemoryAdapter
    ): void {
        $this->addDataProvider('in_memory', $inMemoryAdapter)->shouldReturn(null);
        $this
            ->shouldThrow(new \Exception('Data provider "unexisting_data_provider" not found'))
            ->during('getDataProvider', ['unexisting_data_provider']);
    }
}

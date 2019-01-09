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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryConfigurationRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryConfigurationRepositorySpec extends ObjectBehavior
{
    public function it_is_a_configuration_repository(): void
    {
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }

    public function it_is_an_in_memory_configuration_repository(): void
    {
        $this->shouldHaveType(InMemoryConfigurationRepository::class);
    }

    public function it_finds_an_empty_configuration_if_nothing_has_been_saved(): void
    {
        $configuration = $this->find();
        $configuration->shouldBeAnInstanceOf(Configuration::class);
        $configuration->getToken()->shouldReturn(null);
    }

    public function it_saves_an_empty_configuration(): void
    {
        $configuration = new Configuration();
        $this->save($configuration);

        $this->find()->shouldReturn($configuration);
    }

    public function it_saves_a_configuration_with_token(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('foo'));
        $this->save($configuration);

        $this->find()->shouldReturn($configuration);
    }

    public function it_clears_the_configuration(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('foo'));
        $this->save($configuration);

        $this->clear();

        $storedConfiguration = $this->find();
        $storedConfiguration->getToken()->shouldReturn(null);
    }
}

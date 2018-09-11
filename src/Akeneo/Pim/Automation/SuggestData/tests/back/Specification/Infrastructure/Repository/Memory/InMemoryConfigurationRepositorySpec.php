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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryConfigurationRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryConfigurationRepositorySpec extends ObjectBehavior
{
    public function it_is_an_in_memory_configuration_repository()
    {
        $this->beConstructedWith();

        $this->shouldHaveType(InMemoryConfigurationRepository::class);
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }

    public function it_finds_a_configuration()
    {
        $configuration = new Configuration(['field' => 'value']);
        $this->beConstructedWith($configuration);

        $this->find()->shouldReturn($configuration);
    }

    public function it_saves_a_configuration()
    {
        $configuration = new Configuration(['field' => 'value']);

        $this->save($configuration);

        $this->find()->shouldReturn($configuration);
    }
}

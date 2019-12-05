<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Cache;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ConfigurationRepositorySpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $realConfigurationRepository): void
    {
        $this->beConstructedWith($realConfigurationRepository);
    }

    public function it_is_a_configuration_repository(): void
    {
        $this->shouldImplement(ConfigurationRepositoryInterface::class);
    }

    public function it_keeps_the_configuration_in_cache(ConfigurationRepositoryInterface $realConfigurationRepository): void
    {
        $configuration = new Configuration();
        $realConfigurationRepository->find()->willReturn($configuration)->shouldBeCalledOnce();

        $this->find()->shouldReturn($configuration);
        $this->find()->shouldReturn($configuration);
        $this->find()->shouldReturn($configuration);
    }
}

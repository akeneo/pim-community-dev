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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

class GetConnectionIsActiveHandlerSpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $configurationRepository): void
    {
        $this->beConstructedWith($configurationRepository);
    }

    public function it_returns_true_if_there_is_a_token(ConfigurationRepositoryInterface $configurationRepository): void
    {
        $token = new Token('a_token');
        $configuration = new Configuration();
        $configuration->setToken($token);
        $configurationRepository->find()->willReturn($configuration);

        $this->handle(new GetConnectionIsActiveQuery())->shouldReturn(true);
    }

    public function it_returns_false_if_there_is_no_token(ConfigurationRepositoryInterface $configurationRepository): void
    {
        $configuration = new Configuration();
        $configurationRepository->find()->willReturn($configuration);

        $this->handle(new GetConnectionIsActiveQuery())->shouldReturn(false);
    }
}

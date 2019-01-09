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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConfigurationHandlerSpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $repository): void
    {
        $this->beConstructedWith($repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetConfigurationHandler::class);
    }

    public function it_queries_a_normalized_configuration(GetConfigurationQuery $query, $repository): void
    {
        $configuration = new Configuration();
        $repository->find()->willReturn($configuration);

        $this->handle($query)->shouldReturn($configuration);
    }
}

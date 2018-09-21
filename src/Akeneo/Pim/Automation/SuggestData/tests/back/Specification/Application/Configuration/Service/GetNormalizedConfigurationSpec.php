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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetNormalizedConfiguration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetNormalizedConfigurationSpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $repository): void
    {
        $this->beConstructedWith($repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetNormalizedConfiguration::class);
    }

    public function it_queries_a_normalized_configuration($repository): void
    {
        $configuration = new Configuration(['foo' => 'bar']);
        $repository->find()->willReturn($configuration);

        $this->retrieve()->shouldReturn([
            'code' => Configuration::PIM_AI_CODE,
            'values' => ['foo' => 'bar'],
        ]);
    }

    public function it_returns_an_empty_array_if_configuration_does_not_exist($repository): void
    {
        $repository->find()->willReturn(null);

        $this->retrieve()->shouldReturn([]);
    }
}

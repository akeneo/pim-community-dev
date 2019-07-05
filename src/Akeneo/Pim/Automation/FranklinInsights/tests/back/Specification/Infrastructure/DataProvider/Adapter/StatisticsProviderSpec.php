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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\StatisticsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\InvalidTokenExceptionFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics\StatisticsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\StatisticsProvider;
use PhpSpec\ObjectBehavior;

class StatisticsProviderSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepo,
        InvalidTokenExceptionFactory $invalidTokenExceptionFactory,
        StatisticsWebService $api
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);

        $this->beConstructedWith($configurationRepo, $invalidTokenExceptionFactory, $api);
    }

    public function it_is_a_statistics_provider(): void
    {
        $this->shouldHaveType(StatisticsProvider::class);
        $this->shouldImplement(StatisticsProviderInterface::class);
    }

    public function it_gets_credits_usage_statistics($api): void
    {
        $statistics = new \Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics(
            [
                'consumed' => 2,
                'left' => 1,
                'total' => 3,
            ]
        );
        $api->getCreditsUsageStatistics()->willReturn($statistics);

        $api->setToken('valid-token')->shouldBeCalled();
        $this->getCreditsUsageStatistics()->shouldBeLike(
            new \Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\CreditsUsageStatistics(
                2, 1, 3
            )
        );
    }

    public function it_throws_a_data_provider_exception_when_server_is_down($api): void
    {
        $thrownException = new FranklinServerException();
        $api->getCreditsUsageStatistics()->willThrow($thrownException);

        $api->setToken('valid-token')->shouldBeCalled();
        $this
            ->shouldThrow(DataProviderException::serverIsDown($thrownException))
            ->during('getCreditsUsageStatistics');
    }

    public function it_throws_a_data_provider_exception_when_token_is_invalid($invalidTokenExceptionFactory, $api): void
    {
        $thrownException = new InvalidTokenException();
        $api->getCreditsUsageStatistics()->willThrow($thrownException);

        $dataProviderException = DataProviderException::authenticationError($thrownException);

        $invalidTokenExceptionFactory->create($thrownException)->willReturn($dataProviderException);

        $api->setToken('valid-token')->shouldBeCalled();
        $this
            ->shouldThrow($dataProviderException)
            ->during('getCreditsUsageStatistics');
    }

    public function it_throws_a_data_provider_exception_when_bad_request_occurs($api): void
    {
        $thrownException = new BadRequestException();
        $api->getCreditsUsageStatistics()->willThrow($thrownException);

        $api->setToken('valid-token')->shouldBeCalled();
        $this
            ->shouldThrow(DataProviderException::badRequestError($thrownException))
            ->during('getCreditsUsageStatistics');
    }
}

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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\KeyFigure;

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\CreditsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics\StatisticsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\KeyFigure\CreditsProvider;
use PhpSpec\ObjectBehavior;

class CreditsProviderSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepo,
        StatisticsWebService $api
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);

        $this->beConstructedWith($configurationRepo, $api);
    }

    public function it_is_a_key_figures_credits_provider(): void
    {
        $this->shouldHaveType(CreditsProvider::class);
        $this->shouldImplement(CreditsProviderInterface::class);
    }

    public function it_gets_credits_usage_statistics($api): void
    {
        $statistics = new CreditsUsageStatistics(
            [
                'consumed' => 2,
                'left' => 1,
                'total' => 3,
            ]
        );
        $api->getCreditsUsageStatistics()->willReturn($statistics);
        $api->setToken('valid-token')->shouldBeCalled();

        $this->getCreditsUsageStatistics()->shouldBeLike(
            new KeyFigureCollection([
                new KeyFigure('credits_consumed', 2),
                new KeyFigure('credits_left', 1),
                new KeyFigure('credits_total', 3),
            ])
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

    public function it_throws_a_data_provider_exception_when_token_is_invalid($api): void
    {
        $thrownException = new InvalidTokenException();
        $api->getCreditsUsageStatistics()->willThrow($thrownException);

        $dataProviderException = DataProviderException::authenticationError($thrownException);

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

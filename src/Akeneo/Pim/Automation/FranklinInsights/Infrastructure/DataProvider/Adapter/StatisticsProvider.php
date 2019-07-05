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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\StatisticsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\InvalidTokenExceptionFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\CreditsUsageStatistics;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics\StatisticsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;

final class StatisticsProvider extends AbstractProvider implements StatisticsProviderInterface
{
    /** @var StatisticsWebService */
    private $api;

    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        InvalidTokenExceptionFactory $invalidTokenExceptionFactory,
        StatisticsWebService $api
    ) {
        parent::__construct($configurationRepository, $invalidTokenExceptionFactory);

        $this->api = $api;
    }

    public function getCreditsUsageStatistics(): CreditsUsageStatistics
    {
        $this->api->setToken($this->getToken());

        try {
            $statistics = $this->api->getCreditsUsageStatistics();
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw $this->invalidTokenExceptionFactory->create($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        return new CreditsUsageStatistics(
            $statistics->getConsumed(),
            $statistics->getLeft(),
            $statistics->getTotal()
        );
    }
}

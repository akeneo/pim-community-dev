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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\KeyFigure;

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\CreditsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics\StatisticsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\AbstractProvider;

final class CreditsProvider extends AbstractProvider implements CreditsProviderInterface
{
    /** @var StatisticsWebService */
    private $api;

    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        StatisticsWebService $api
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
    }

    public function getCreditsUsageStatistics(): KeyFigureCollection
    {
        $this->api->setToken($this->getToken());

        try {
            $statistics = $this->api->getCreditsUsageStatistics();
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        return new KeyFigureCollection([
            new KeyFigure('credits_consumed', $statistics->getConsumed()),
            new KeyFigure('credits_left', $statistics->getLeft()),
            new KeyFigure('credits_total', $statistics->getTotal()),
        ]);
    }
}

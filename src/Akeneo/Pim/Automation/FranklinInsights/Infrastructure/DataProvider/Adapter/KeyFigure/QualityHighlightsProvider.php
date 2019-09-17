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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights\QualityHighlightsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\AbstractProvider;

class QualityHighlightsProvider extends AbstractProvider implements QualityHighlightsProviderInterface
{
    private $api;

    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        QualityHighlightsWebService $api
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
    }

    public function getKeyFigures(): KeyFigureCollection
    {
        $this->api->setToken($this->getToken());

        try {
            $qualityHighlightMetrics = $this->api->getMetrics();
        } catch (FranklinServerException $exception) {
            throw DataProviderException::serverIsDown($exception);
        } catch (InvalidTokenException $exception) {
            throw DataProviderException::authenticationError($exception);
        } catch (BadRequestException $exception) {
            throw DataProviderException::badRequestError($exception);
        }

        return new KeyFigureCollection([
            new KeyFigure('value_validated', $qualityHighlightMetrics->getValueValidated()),
            new KeyFigure('value_in_error', $qualityHighlightMetrics->getValueInError()),
            new KeyFigure('value_suggested', $qualityHighlightMetrics->getValueSuggested()),
            new KeyFigure('name_and_value_suggested', $qualityHighlightMetrics->getNameAndValueSuggested()),
        ]);
    }
}

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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights\QualityHighlightsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\QualityHighlightsMetrics;
use PhpSpec\ObjectBehavior;

class QualityHighlightsProviderSpec extends ObjectBehavior
{
    public function let(ConfigurationRepositoryInterface $configurationRepository, QualityHighlightsWebService $api): void
    {
        $this->beConstructedWith($configurationRepository, $api);

        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepository->find()->willReturn($configuration);
    }

    public function it_is_a_key_figures_quality_highlights_provider(): void
    {
        $this->shouldImplement(QualityHighlightsProviderInterface::class);
    }

    public function it_provides_key_figures(QualityHighlightsWebService $api): void
    {
        $api->setToken('valid-token')->shouldBeCalled();
        $api->getMetrics()->willReturn(new QualityHighlightsMetrics([
            'added' => 10,
            'not_mapped' => 1,
            'value_not_validated' => 2,
            'value_added' => 4,
            'value_mismatch' => 3,
            'value_validated' => 5
        ]));

        $this->getKeyFigures()->shouldBeLike(new KeyFigureCollection([
            new KeyFigure('franklin_values_validated', 5),
            new KeyFigure('franklin_values_in_error', 3),
            new KeyFigure('franklin_values_suggested', 4),
            new KeyFigure('franklin_names_and_values_suggested', 10),
        ]));
    }

    public function it_throws_an_exception_if_an_error_occurred(QualityHighlightsWebService $api): void
    {
        $api->setToken('valid-token')->shouldBeCalled();
        $api->getMetrics()->willThrow(new FranklinServerException());

        $this->shouldThrow(DataProviderException::class)->during('getKeyFigures');
    }
}

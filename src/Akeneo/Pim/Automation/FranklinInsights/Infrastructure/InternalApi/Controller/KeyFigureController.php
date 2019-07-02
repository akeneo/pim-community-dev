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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetCreditsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetCreditsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyFiguresHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyFiguresQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\CreditUsageStatisticsNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\KeyFigureCollectionNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class KeyFigureController
{
    /** @var GetKeyFiguresHandler */
    private $getKeyFiguresHandler;

    /** @var GetCreditsHandler */
    private $GetCreditsHandler;

    public function __construct(
        GetKeyFiguresHandler $getKeyFiguresHandler,
        GetCreditsHandler $GetCreditsHandler
    ) {
        $this->getKeyFiguresHandler = $getKeyFiguresHandler;
        $this->GetCreditsHandler = $GetCreditsHandler;
    }

    public function getAllAction(): Response
    {
        $keyFigureCollection = $this->getKeyFiguresHandler->handle(new GetKeyFiguresQuery());
        $creditUsageStatistics = $this->GetCreditsHandler->handle(new GetCreditsQuery());

        $keyFigureCollectionNormalizer = new KeyFigureCollectionNormalizer();
        $creditUsageStatisticsNormalizer = new CreditUsageStatisticsNormalizer();

        return new JsonResponse(
            array_merge(
                $keyFigureCollectionNormalizer->normalize($keyFigureCollection),
                $creditUsageStatisticsNormalizer->normalize($creditUsageStatistics)
            )
        );
    }
}

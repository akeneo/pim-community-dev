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

use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetAskFranklinCreditsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetAskFranklinCreditsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyMeasurementsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query\GetKeyMeasurementsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\KeyFigureCollectionNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class KeyFigureController
{
    /** @var GetKeyMeasurementsHandler */
    private $getKeyMeasurementsHandler;

    /** @var GetAskFranklinCreditsHandler */
    private $getAskFranklinCreditsHandler;

    /** @var KeyFigureCollectionNormalizer */
    private $keyFigureCollectionNormalizer;

    public function __construct(
        GetKeyMeasurementsHandler $getKeyMeasurementsHandler,
        GetAskFranklinCreditsHandler $getAskFranklinCreditsHandler,
        KeyFigureCollectionNormalizer $keyFigureCollectionNormalizer
    ) {
        $this->getKeyMeasurementsHandler = $getKeyMeasurementsHandler;
        $this->getAskFranklinCreditsHandler = $getAskFranklinCreditsHandler;
        $this->keyFigureCollectionNormalizer = $keyFigureCollectionNormalizer;
    }

    public function getAllAction(): Response
    {
        return new JsonResponse(
            array_merge(
                $this->keyFigureCollectionNormalizer->normalize(
                    $this->getKeyMeasurementsHandler->handle(new GetKeyMeasurementsQuery())
                ),
                $this->keyFigureCollectionNormalizer->normalize(
                    $this->getAskFranklinCreditsHandler->handle(new GetAskFranklinCreditsQuery())
                )
            )
        );
    }
}

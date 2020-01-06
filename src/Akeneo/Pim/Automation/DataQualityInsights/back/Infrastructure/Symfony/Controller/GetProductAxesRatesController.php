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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetProductAxesRatesController
{
    /** @var GetProductAxesRates */
    private $getProductAxesRates;

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(GetProductAxesRates $getProductAxesRates, FeatureFlag $featureFlag)
    {
        $this->getProductAxesRates = $getProductAxesRates;
        $this->featureFlag = $featureFlag;
    }

    public function __invoke(string $productId): JsonResponse
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $productId = new ProductId(intval($productId));
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $axesRates = $this->getProductAxesRates->get($productId);

        return new JsonResponse($axesRates);
    }
}

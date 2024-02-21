<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelQualityScoreController
{
    public function __construct(private GetProductModelScores $getProductModelScores)
    {
    }

    public function __invoke(string $productId): JsonResponse
    {
        try {
            return new JsonResponse($this->getProductModelScores->get(ProductModelId::fromString($productId)));
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable) {
            return new JsonResponse(
                ['message' => 'Cannot get product model score.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelEvaluationController
{
    /** @var GetProductEvaluation */
    private $getProductModelEvaluation;

    public function __construct(GetProductEvaluation $getProductModelEvaluation)
    {
        $this->getProductModelEvaluation = $getProductModelEvaluation;
    }

    public function __invoke(string $productId): Response
    {
        try {
            $evaluation = $this->getProductModelEvaluation->get(
                ProductUuid::fromString($productId)
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($evaluation);
    }
}

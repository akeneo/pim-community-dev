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
class GetProductEvaluationController
{
    /** @var GetProductEvaluation */
    private $getProductEvaluation;

    public function __construct(GetProductEvaluation $getProductEvaluation)
    {
        $this->getProductEvaluation = $getProductEvaluation;
    }

    public function __invoke(string $productUuid): Response
    {
        try {
            $evaluation = $this->getProductEvaluation->get(
                ProductUuid::fromString($productUuid)
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($evaluation);
    }
}

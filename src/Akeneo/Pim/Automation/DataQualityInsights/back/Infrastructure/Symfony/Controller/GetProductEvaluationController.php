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

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductEvaluationController
{
    /** @var GetProductEvaluation */
    private $getProductEvaluation;

    public function __construct(GetProductEvaluation $getProductEvaluation)
    {
        $this->getProductEvaluation = $getProductEvaluation;
    }

    public function __invoke(string $productId): Response
    {
        try {
            $evaluation = $this->getProductEvaluation->get(
                new ProductId(intval($productId))
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($evaluation);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetAttributeSpellcheckEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeSpellcheckEvaluationController
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var GetAttributeSpellcheckEvaluation */
    private $getAttributeSpellcheckEvaluation;

    public function __construct(
        FeatureFlag $featureFlag,
        GetAttributeSpellcheckEvaluation $getAttributeSpellcheckEvaluation
    ) {
        $this->featureFlag = $featureFlag;
        $this->getAttributeSpellcheckEvaluation = $getAttributeSpellcheckEvaluation;
    }

    public function __invoke(Request $request, string $attributeCode)
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $evaluation = $this->getAttributeSpellcheckEvaluation->get(new AttributeCode($attributeCode));

        return new JsonResponse($evaluation);
    }
}

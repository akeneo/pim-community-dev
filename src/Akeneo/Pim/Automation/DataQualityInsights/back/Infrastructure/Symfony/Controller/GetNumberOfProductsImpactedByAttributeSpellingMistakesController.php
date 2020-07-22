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
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetNumberOfProductsImpactedByAttributeSpellingMistakesController
{
    private $featureFlag;

    private $getNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes;

    public function __construct(
        FeatureFlag $featureFlag,
        GetNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes $getNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes
    ) {
        $this->featureFlag = $featureFlag;
        $this->getNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes = $getNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes;
    }

    public function __invoke(Request $request, string $attributeCode)
    {
        if (! $this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->getNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes->byAttributeCode(new AttributeCode($attributeCode))
        );
    }
}

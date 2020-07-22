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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeOptionsSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeOptionsLabelsErrorCountController
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var GetAllAttributeOptionsSpellcheckQueryInterface */
    private $attributeOptionsSpellcheckQuery;

    public function __construct(FeatureFlag $featureFlag, GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery)
    {
        $this->featureFlag = $featureFlag;
        $this->attributeOptionsSpellcheckQuery = $attributeOptionsSpellcheckQuery;
    }

    public function __invoke(Request $request, string $attributeCode)
    {
        if (! $this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $attributeOptionsSpellcheck = $this->attributeOptionsSpellcheckQuery->byAttributeCode(new AttributeCode($attributeCode));

        $count = array_reduce($attributeOptionsSpellcheck, function (int $previousCount, AttributeOptionSpellcheck $attributeOptionSpellcheck) {
            return $previousCount + $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber();
        }, 0);

        return new JsonResponse($count);
    }
}

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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeLabelsErrorCountController
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var GetAttributeSpellcheckQueryInterface */
    private $getAttributeSpellcheckQuery;

    public function __construct(FeatureFlag $featureFlag, GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery)
    {
        $this->featureFlag = $featureFlag;
        $this->getAttributeSpellcheckQuery = $getAttributeSpellcheckQuery;
    }

    public function __invoke(Request $request, string $attributeCode)
    {
        if (! $this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $attributeSpellcheck = $this->getAttributeSpellcheckQuery->getByAttributeCode(new AttributeCode($attributeCode));

        if ($attributeSpellcheck === null) {
            return new JsonResponse(0);
        }

        return new JsonResponse($attributeSpellcheck->getResult()->getLabelsToImproveNumber());
    }
}

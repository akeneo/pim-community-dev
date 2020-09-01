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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllAttributeOptionsSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetAttributeOptionsLabelsErrorCountController
{
    /** @var GetAllAttributeOptionsSpellcheckQueryInterface */
    private $attributeOptionsSpellcheckQuery;

    public function __construct(GetAllAttributeOptionsSpellcheckQueryInterface $attributeOptionsSpellcheckQuery)
    {
        $this->attributeOptionsSpellcheckQuery = $attributeOptionsSpellcheckQuery;
    }

    public function __invoke(Request $request, string $attributeCode)
    {
        $attributeOptionsSpellcheck = $this->attributeOptionsSpellcheckQuery->byAttributeCode(new AttributeCode($attributeCode));

        $count = array_reduce($attributeOptionsSpellcheck, function (int $previousCount, AttributeOptionSpellcheck $attributeOptionSpellcheck) {
            return $previousCount + $attributeOptionSpellcheck->getResult()->getLabelsToImproveNumber();
        }, 0);

        return new JsonResponse($count);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetAttributesRulesNumber;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetAttributesRulesNumberController
{
    private GetAttributesRulesNumber $getAttributesRulesNumberQuery;

    public function __construct(GetAttributesRulesNumber $getAttributesRulesNumberQuery)
    {
        $this->getAttributesRulesNumberQuery = $getAttributesRulesNumberQuery;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $attributeCodes = $request->query->get('attributes');

        $attributesRulesNumber = [];
        if (is_array($attributeCodes) && !empty($attributeCodes)) {
            $attributesRulesNumber = $this->getAttributesRulesNumberQuery->execute($attributeCodes);
        }

        return new JsonResponse($attributesRulesNumber);
    }
}

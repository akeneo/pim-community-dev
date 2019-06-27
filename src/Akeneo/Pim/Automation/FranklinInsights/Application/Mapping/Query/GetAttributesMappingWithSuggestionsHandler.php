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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor\ApplyAttributeExactMatches;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor\SuggestAttributeExactMatchesFromOtherFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsHandler
{

    private $getAttributesMappingByFamilyHandler;

    private $applyAttributeExactMatches;

    private $suggestAttributeExactMatchesFromOtherFamilyDataProcessor;

    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        ApplyAttributeExactMatches $applyAttributeExactMatches,
        SuggestAttributeExactMatchesFromOtherFamily $suggestAttributeExactMatchesFromOtherFamilyDataProcessor
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->applyAttributeExactMatches = $applyAttributeExactMatches;
        $this->suggestAttributeExactMatchesFromOtherFamilyDataProcessor = $suggestAttributeExactMatchesFromOtherFamilyDataProcessor;
    }

    public function handle(GetAttributesMappingWithSuggestionsQuery $query): AttributeMappingCollection
    {
        $familyCode = $query->getFamilyCode();
        $attributeMappingCollection = $this->getAttributesMappingByFamilyHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        $attributeMappingCollection = $this->applyAttributeExactMatches->process($attributeMappingCollection, $familyCode);
        $attributeMappingCollection = $this->suggestAttributeExactMatchesFromOtherFamilyDataProcessor->process($attributeMappingCollection, $familyCode);

        return $attributeMappingCollection;
    }
}

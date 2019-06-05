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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsHandler
{
    /** @var SelectExactMatchAttributeCodeQueryInterface */
    private $selectExactMatchAttributeCodeQuery;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingByFamilyHandler;

    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
    ) {
        $this->selectExactMatchAttributeCodeQuery = $selectExactMatchAttributeCodeQuery;
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
    }

    public function handle(GetAttributesMappingWithSuggestionsQuery $query): AttributesMappingResponse
    {
        $familyAttributesMapping = $this->getAttributesMappingByFamilyHandler->handle(
            new GetAttributesMappingByFamilyQuery($query->getFamilyCode())
        );

        $suggestedPimAttributeCodes = $this->findSuggestedPimAttributeCodes($query->getFamilyCode(), $familyAttributesMapping);
        $newMapping = new AttributesMappingResponse();

        foreach ($familyAttributesMapping as $attributeMapping) {
            $pimAttributeCode = $attributeMapping->getPimAttributeCode();
            if ($attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $suggestedPimAttributeCodes)
            ) {
                $pimAttributeCode = $suggestedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()];
            }

            $newAttributeMapping = new AttributeMapping(
                $attributeMapping->getTargetAttributeCode(),
                $attributeMapping->getTargetAttributeLabel(),
                $attributeMapping->getTargetAttributeType(),
                $pimAttributeCode,
                $attributeMapping->getStatus(),
                $attributeMapping->getSummary()
            );
            $newMapping->addAttribute($newAttributeMapping);
        }

        return $newMapping;
    }

    /**
     * @return string[]
     */
    private function findSuggestedPimAttributeCodes(FamilyCode $familyCode, AttributesMappingResponse $familyAttributesMapping): array
    {
        $suggestedPimAttributeCodes = $this->selectExactMatchAttributeCodeQuery->execute(
            $familyCode,
            $familyAttributesMapping->getPendingAttributesFranklinLabels()
        );

        return array_filter($suggestedPimAttributeCodes, function ($attributeCode) use ($familyAttributesMapping) {
            return null === $attributeCode || !$familyAttributesMapping->hasPimAttribute(new AttributeCode($attributeCode));
        });
    }
}

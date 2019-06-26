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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor\AttributeMappingCollectionDataProcessorInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingWithSuggestionsHandler
{
    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingByFamilyHandler;

    /** @var AttributeMappingCollectionDataProcessorInterface */
    private $attributeMappingDataProcessor;

    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        AttributeMappingCollectionDataProcessorInterface $attributeMappingDataProcessor
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->attributeMappingDataProcessor = $attributeMappingDataProcessor;
    }

    public function handle(GetAttributesMappingWithSuggestionsQuery $query): AttributeMappingCollection
    {
        $familyCode = $query->getFamilyCode();
        $attributeMappingCollection = $this->getAttributesMappingByFamilyHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        return $this->attributeMappingDataProcessor->process($attributeMappingCollection, [
            'familyCode' => $familyCode,
        ]);
    }
}

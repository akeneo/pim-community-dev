<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributesMappingProviderInterface */
    private $attributesMappingProvider;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var SelectFamilyAttributeCodesQueryInterface */
    private $selectFamilyAttributeCodesQuery;

    /**
     * @param FamilyRepositoryInterface                $familyRepository
     * @param AttributeRepositoryInterface             $attributeRepository
     * @param AttributesMappingProviderInterface       $attributesMappingProvider
     * @param ProductSubscriptionRepositoryInterface   $subscriptionRepository
     * @param SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributesMappingProviderInterface $attributesMappingProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->selectFamilyAttributeCodesQuery = $selectFamilyAttributeCodesQuery;
    }

    /**
     * @param SaveAttributesMappingByFamilyCommand $command
     *
     * @throws AttributeMappingException
     * @throws DataProviderException
     */
    public function handle(SaveAttributesMappingByFamilyCommand $command): void
    {
        $familyCode = new FamilyCode($command->getFamilyCode());
        if (false === $this->familyRepository->exist($familyCode)) {
            throw new \InvalidArgumentException(sprintf('Family "%s" not found', $familyCode));
        }

        $mappedAttrCodes = [];
        foreach ($command->getMapping() as $attributeMapping) {
            if (!empty($attributeMapping['attribute'])) {
                $mappedAttrCodes[] = $attributeMapping['attribute'];
            }
        }

        $familyAttributeCodes = $this->selectFamilyAttributeCodesQuery->execute($familyCode);
        $mappedAttrCodes = array_intersect($mappedAttrCodes, $familyAttributeCodes);

        $attributes = [];
        foreach ($this->attributeRepository->findByCodes($mappedAttrCodes) as $attribute) {
            $attributes[(string) $attribute->getCode()] = $attribute;
        }

        if (empty($attributes)) {
            throw AttributeMappingException::emptyAttributesMapping();
        }

        $attributesMapping = new AttributesMapping((string) $familyCode);
        foreach ($command->getMapping() as $franklinAttrId => $attributeMapping) {
            $attributesMapping->map(
                $franklinAttrId,
                $attributeMapping['franklinAttribute']['type'],
                $attributes[$attributeMapping['attribute']] ?? null
            );
        }

        $this->attributesMappingProvider->saveAttributesMapping((string) $familyCode, $attributesMapping);
        $this->subscriptionRepository->emptySuggestedDataAndMissingMappingByFamily((string) $familyCode);
    }
}

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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

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

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributesMappingProviderInterface $attributesMappingProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param SaveAttributesMappingByFamilyCommand $command
     *
     * @throws AttributeMappingException
     * @throws DataProviderException
     */
    public function handle(SaveAttributesMappingByFamilyCommand $command): void
    {
        $familyCode = $command->getFamilyCode();
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new \InvalidArgumentException(sprintf('Family "%s" not found', $familyCode));
        }

        $mappedAttrCodes = [];
        foreach ($command->getMapping() as $attributeMapping) {
            if (!empty($attributeMapping['attribute'])) {
                $mappedAttrCodes[] = $attributeMapping['attribute'];
            }
        }
        $mappedAttrCodes = array_intersect($mappedAttrCodes, $family->getAttributeCodes());

        $attributes = [];
        foreach ($this->attributeRepository->findBy(['code' => $mappedAttrCodes]) as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        if (empty($attributes)) {
            throw AttributeMappingException::emptyAttributesMapping();
        }

        $attributesMapping = new AttributesMapping($familyCode);
        foreach ($command->getMapping() as $franklinAttrId => $attributeMapping) {
            $attributesMapping->map(
                $franklinAttrId,
                $attributeMapping['franklinAttribute']['type'],
                $attributes[$attributeMapping['attribute']] ?? null
            );
        }

        $this->attributesMappingProvider->saveAttributesMapping($familyCode, $attributesMapping);
        $this->subscriptionRepository->emptySuggestedDataAndMissingMappingByFamily($familyCode);
    }
}

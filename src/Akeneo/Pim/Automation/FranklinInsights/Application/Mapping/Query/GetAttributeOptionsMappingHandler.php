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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingHandler
{
    /** @var AttributeOptionsMappingProviderInterface */
    private $attributeOptionsMappingProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(
        AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->attributeOptionsMappingProvider = $attributeOptionsMappingProvider;
        $this->familyRepository = $familyRepository;
    }

    /**
     * @param GetAttributeOptionsMappingQuery $query
     *
     * @return AttributeOptionsMapping
     */
    public function handle(GetAttributeOptionsMappingQuery $query): AttributeOptionsMapping
    {
        if (!$this->familyRepository->findOneByIdentifier((string) $query->familyCode()) instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf('Family "%s" does not exist', $query->familyCode())
            );
        }

        $attributeOptionsMapping = $this->attributeOptionsMappingProvider->getAttributeOptionsMapping(
            $query->familyCode(),
            $query->franklinAttributeId()
        );
        $attributeOptionsMapping->sort();

        return $attributeOptionsMapping;
    }
}

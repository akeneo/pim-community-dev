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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class UpdateAttributesMappingByFamilyHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function handle(UpdateAttributesMappingByFamilyCommand $command)
    {
        //TODO: Validates object
            // Validates that attribute exists
            // Validates that family exists
        $this->validate($command);
        //TODO: Completes the AttributeMapping class

        // TODO: Calls Data Provider
    }

    private function validate(UpdateAttributesMappingByFamilyCommand $command)
    {
        $familyCode = $command->getFamilyCode();
        if (null === $this->familyRepository->findOneByIdentifier($familyCode)) {
            throw new \InvalidArgumentException(sprintf('Family "%s" not found', $familyCode));
        }

        $attributesMapping = $command->getAttributesMapping();
        foreach ($attributesMapping as $attributeMapping) {
            if (null !== $attributeMapping->getPimAttributeCode()) {
                if (null === $this->attributeRepository->findOneByIdentifier($attributeMapping->getPimAttributeCode())) {
                    throw new \InvalidArgumentException(sprintf('Attribute "%s" not found', $familyCode));
                }
            }
        }
    }
}

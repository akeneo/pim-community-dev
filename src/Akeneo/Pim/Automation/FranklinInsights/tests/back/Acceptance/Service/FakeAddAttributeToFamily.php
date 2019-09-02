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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Service;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeAddAttributeToFamily implements AddAttributeToFamilyInterface
{
    private $attributeRepository;

    private $familyRepository;

    public function __construct(
        InMemoryAttributeRepository $attributeRepository,
        InMemoryFamilyRepository $familyRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
    }

    public function addAttributeToFamily(AttributeCode $attributeCode, FamilyCode $familyCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier((string) $attributeCode);
        $family = $this->familyRepository->findOneByIdentifier((string) $familyCode);
        $family->addAttribute($attribute);
    }

    public function bulkAddAttributesToFamily(FamilyCode $familyCode, array $attributeCodes): void
    {
        $family = $this->familyRepository->findOneByIdentifier((string) $familyCode);
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier((string) $attributeCode);
            $family->addAttribute($attribute);
        }
    }
}

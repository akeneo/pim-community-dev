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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface as StructureFamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface as StructureFamilyRepositoryInterface;

class InMemoryFamilyRepository implements FamilyRepositoryInterface
{
    /** @var StructureFamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param StructureFamilyRepositoryInterface $familyRepository
     */
    public function __construct(StructureFamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    public function findOneByIdentifier(FamilyCode $familyCode): ?Family
    {
        $structureFamily = $this->familyRepository->findOneByIdentifier((string) $familyCode);

        return $structureFamily instanceof StructureFamilyInterface ? $this->buildFamily($structureFamily) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function exist(FamilyCode $familyCode): bool
    {
        $family = $this->familyRepository->findOneByIdentifier((string) $familyCode);

        return $family !== null;
    }

    /**
     * @param StructureFamilyInterface $structureFamily
     *
     * @return Family
     */
    private function buildFamily(StructureFamilyInterface $structureFamily): Family
    {
        $labels = [];
        foreach ($structureFamily->getTranslations() as $translation) {
            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return new Family(new FamilyCode($structureFamily->getCode()), $labels);
    }
}

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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class InMemorySelectFamilyAttributeCodesQuery implements SelectFamilyAttributeCodesQueryInterface
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * @param FamilyCode $familyCode
     *
     * @return string[] The codes of the attributes of the given family.
     */
    public function execute(FamilyCode $familyCode): array
    {
        $family = $this->familyRepository->findOneByIdentifier((string) $familyCode);

        return $family instanceof FamilyInterface ? $family->getAttributeCodes() : [];
    }
}

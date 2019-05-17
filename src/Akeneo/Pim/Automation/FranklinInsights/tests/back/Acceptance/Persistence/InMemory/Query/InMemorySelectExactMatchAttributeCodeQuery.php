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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemorySelectExactMatchAttributeCodeQuery implements SelectExactMatchAttributeCodeQueryInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function execute(FamilyCode $familyCode, array $franklinAttributeLabels): array
    {
        $result = array_fill_keys($franklinAttributeLabels, null);

        foreach ($franklinAttributeLabels as $franklinAttributeLabel) {
            $attribute = $this->attributeRepository->findOneByIdentifier(strtolower((string) $franklinAttributeLabel));

            $matchingAttribute = null;

            if ($attribute instanceof AttributeInterface) {
                $matchingAttribute = $attribute;
            } else {
                $attributes = $this->attributeRepository->findBy(['label' => (string) $franklinAttributeLabel]);
                if (count($attributes) > 0) {
                    $matchingAttribute = $attributes[0]->getCode();
                }
            }

            if ($matchingAttribute instanceof AttributeInterface) {
                if (!$matchingAttribute->isLocaleSpecific() &&
                    !$matchingAttribute->isLocalizable() &&
                    !$matchingAttribute->isScopable() &&
                    $this->hasFamily($attribute->getFamilies(), $familyCode)
                ) {
                    $result[$franklinAttributeLabel] = $matchingAttribute->getCode();
                }
            }
        }

        return $result;
    }

    private function hasFamily(iterable $families, FamilyCode $familyCode)
    {
        foreach ($families as $family) {
            if ((string) $familyCode === $family->getCode()) {
                return true;
            }
        }

        return false;
    }
}

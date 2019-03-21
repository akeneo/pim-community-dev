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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as PimAttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class InMemoryAttributeRepository implements AttributeRepositoryInterface
{
    /** @var PimAttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(PimAttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function findByCodes(array $codes): array
    {
        $attributes = [];

        foreach ($this->attributeRepository->findBy(['code' => $codes]) as $attribute) {
            $attributes[] = $this->buildAttribute($attribute);
        }

        return $attributes;
    }

    public function findOneByIdentifier(string $code): ?Attribute
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        if ($attribute instanceof AttributeInterface) {
            return $this->buildAttribute($attribute);
        }

        return null;
    }

    public function getAttributeTypeByCodes(array $codes): array
    {
        return $this->attributeRepository->getAttributeTypeByCodes($codes);
    }

    public function save($attribute): void
    {
        $this->attributeRepository->save($attribute);
    }

    /**
     * @param $attribute
     *
     * @return Attribute
     */
    private function buildAttribute(AttributeInterface $attribute): Attribute
    {
        $labels = [];
        foreach ($attribute->getTranslations() as $translation) {
            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return new Attribute(
            new AttributeCode($attribute->getCode()),
            random_int(0, 999),
            $attribute->getType(),
            (bool) $attribute->isScopable(),
            (bool) $attribute->isLocalizable(),
            (bool) $attribute->isDecimalsAllowed(),
            (bool) $attribute->isLocaleSpecific(),
            $labels,
            $attribute->getMetricFamily(),
            $attribute->getDefaultMetricUnit()
        );
    }
}

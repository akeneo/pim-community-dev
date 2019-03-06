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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class InMemoryAttributeRepository implements AttributeRepositoryInterface
{
    /** @var \Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(\Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function findByCodes(array $codes)
    {
        $attributes = [];

        foreach ($this->attributeRepository->findBy(['code' => $codes]) as $attribute) {
            $attributes[] = $this->buildAttribute($attribute);
        }

        return $attributes;
    }

    public function findOneByIdentifier(string $attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if ($attribute instanceof AttributeInterface) {
            return $this->buildAttribute($attribute);
        }

        return null;
    }

    public function getAttributeTypeByCodes(array $attributeCodes): array
    {
        return $this->attributeRepository->getAttributeTypeByCodes($attributeCodes);
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

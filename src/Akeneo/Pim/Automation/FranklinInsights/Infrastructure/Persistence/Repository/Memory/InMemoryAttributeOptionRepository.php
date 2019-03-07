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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Structure\Component\Model\AttributeOption as PimAttributeOption;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class InMemoryAttributeOptionRepository implements AttributeOptionRepositoryInterface
{
    /** @var PimAttributeOption[] */
    private $attributeOptionCollection = [];

    public function findOneByIdentifier(string $code): ?AttributeOption
    {
        throw new NotImplementedException('findOneByIdentifier');
    }

    public function findCodesByIdentifiers(string $attributeCode, array $attributeOptionCodes): array
    {
        $query = \array_filter(
            $this->attributeOptionCollection,
            function (PimAttributeOption $attributeOption) use ($attributeCode, $attributeOptionCodes) {
                return $attributeOption->getAttribute()->getCode() === $attributeCode &&
                    false !== \array_search($attributeOption->getCode(), $attributeOptionCodes);
            }
        );

        return \array_map(
            function (PimAttributeOption $attributeOption) {
                return $attributeOption->getCode();
            },
            $query
        );
    }

    public function findByCode(array $codes): array
    {
        $pimAttributeOptions = \array_filter(
            $this->attributeOptionCollection,
            function (PimAttributeOption $attributeOption) use ($codes) {
                return \array_search($attributeOption->getCode(), $codes);
            }
        );

        return \array_map(
            function (PimAttributeOption $pimAttributeOption) {
                $translations = [];
                foreach ($pimAttributeOption->getOptionValues() as $optionValue) {
                    $translations[$optionValue->getLocale()] = $optionValue->getValue();
                }

                return new AttributeOption(
                    $pimAttributeOption->getCode(),
                    new AttributeCode($pimAttributeOption->getAttribute()->getCode()),
                    $translations
                );
            },
            $pimAttributeOptions
        );
    }

    public function save(PimAttributeOption $attributeOption): void
    {
        $this->attributeOptionCollection[] = $attributeOption;
    }
}

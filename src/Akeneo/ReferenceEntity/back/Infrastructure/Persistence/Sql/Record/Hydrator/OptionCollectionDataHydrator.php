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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueDataInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class OptionCollectionDataHydrator implements DataHydratorInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof OptionCollectionAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $filteredOptions = $this->keepExistingOptionsOnly($normalizedData, $attribute);
        if (empty($filteredOptions)) {
            return EmptyData::create();
        }

        return OptionCollectionData::createFromNormalize($filteredOptions);
    }

    private function keepExistingOptionsOnly(
        array $optionsFromDatabase,
        OptionCollectionAttribute $optionCollectionAttribute
    ): array {
        return array_filter(
            $optionsFromDatabase,
            function (string $optionFromDatabase) use ($optionCollectionAttribute) {
                return $optionCollectionAttribute->hasAttributeOption(OptionCode::fromString($optionFromDatabase));
            }
        );
    }
}

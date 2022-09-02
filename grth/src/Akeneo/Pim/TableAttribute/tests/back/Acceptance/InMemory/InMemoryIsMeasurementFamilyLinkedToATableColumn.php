<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;

final class InMemoryIsMeasurementFamilyLinkedToATableColumn implements IsMeasurementFamilyLinkedToATableColumn
{
    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function forCode(string $code): bool
    {
        $tableAttributes = $this->attributeRepository->findBy(['attributeType' => AttributeTypes::TABLE]);

        /** @var AttributeInterface $tableAttribute */
        foreach ($tableAttributes as $tableAttribute) {
            foreach ($tableAttribute->getRawTableConfiguration() as $rawColumnDefinition) {
                if ($rawColumnDefinition['data_type'] === MeasurementColumn::DATATYPE) {
                    if (\strtolower($rawColumnDefinition['measurement_family_code']) === \strtolower($code)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}

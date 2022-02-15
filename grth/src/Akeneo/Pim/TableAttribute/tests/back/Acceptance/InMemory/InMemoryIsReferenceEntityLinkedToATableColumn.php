<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;

final class InMemoryIsReferenceEntityLinkedToATableColumn implements IsReferenceEntityLinkedToATableColumn
{
    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function forIdentifier(string $identifier): bool
    {
        $tableAttributes = $this->attributeRepository->findBy(['attributeType' => AttributeTypes::TABLE]);

        /** @var AttributeInterface $tableAttribute */
        foreach ($tableAttributes as $tableAttribute) {
            foreach ($tableAttribute->getRawTableConfiguration() as $rawColumnDefinition) {
                if ($rawColumnDefinition['data_type'] === ReferenceEntityColumn::DATATYPE) {
                    if (\strtolower($rawColumnDefinition['reference_entity_identifier']) === \strtolower($identifier)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}

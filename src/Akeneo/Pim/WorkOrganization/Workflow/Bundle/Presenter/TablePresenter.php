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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;

final class TablePresenter extends AbstractProductValuePresenter
{
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return $attributeType === AttributeTypes::TABLE;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $result = parent::present($formerData, $change);
        $result['attributeCode'] = $change['attribute'];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (!$data instanceof Table) {
            return [];
        }

        $normalizedTable = $data->normalize();

        foreach ($normalizedTable as $index => $row) {
            foreach ($row as $stringId => $value) {
                $columnId = ColumnId::fromString($stringId);
                unset($normalizedTable[$index][$stringId]);
                $normalizedTable[$index][$columnId->extractColumnCode()->asString()] = $value;
            }
        }

        return $normalizedTable;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}

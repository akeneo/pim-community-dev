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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter;

class TablePresenter extends AbstractProductValuePresenter
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
            return null;
        }

        return $data->normalize();
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}

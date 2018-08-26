<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductRowsFromIdentifiers implements CursorableRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $rows = [];
        foreach ($identifiers as $identifier) {
            $id = rand(1, 1000);
            $rows[] = new ReadModel\Row(
                $identifier,
                'family',
                ['group_1', 'group_2'],
                true,
                new \DateTime(),
                new \DateTime(),
                null,
                null,
                10,
                IdEncoder::PRODUCT_TYPE,
                $id,
                IdEncoder::encode(IdEncoder::PRODUCT_TYPE, $id),
                true,
                null,
                null,
                new ValueCollection([])
            );

        }

        return $rows;
    }
}

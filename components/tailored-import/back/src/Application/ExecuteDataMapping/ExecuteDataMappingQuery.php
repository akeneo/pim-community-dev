<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteDataMappingQuery
{
    public function __construct(
        private Row $row,
        private DataMappingCollection $dataMappingCollection,
    ) {
    }

    public function getRow(): Row
    {
        return $this->row;
    }

    public function getDataMappingCollection(): DataMappingCollection
    {
        return $this->dataMappingCollection;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;

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

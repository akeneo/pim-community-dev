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

namespace Akeneo\Pim\TableAttribute\tests\back\Acceptance\InMemory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetNonExistingSelectOptionCodes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;

class InMemoryGetNonExistingSelectOptionCodes implements GetNonExistingSelectOptionCodes
{
    private InMemorySelectOptionCollectionRepository $collectionRepository;

    public function __construct(InMemorySelectOptionCollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
    }

    public function forOptionCodes(string $attributeCode, ColumnCode $columnCode, array $selectOptionCodes): array
    {
        return array_diff($selectOptionCodes, $this->collectionRepository->getByColumn($attributeCode, $columnCode)->getOptionCodes());
    }
}

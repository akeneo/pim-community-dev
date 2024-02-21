<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindFamiliesWithLabels
{
    /**
     * @return FamilyWithLabels[]
     */
    public function fromQuery(FamilyQuery $query): array;
}

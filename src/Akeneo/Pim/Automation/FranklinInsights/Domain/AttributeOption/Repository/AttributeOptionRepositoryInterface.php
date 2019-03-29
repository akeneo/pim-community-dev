<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
interface AttributeOptionRepositoryInterface
{
    /**
     * Find an attribute option by identifier.
     */
    public function findOneByIdentifier(AttributeCode $attributeCode, string $attributeOptionCode): ?AttributeOption;

    /**
     * Find attribute options by codes.
     *
     * @deprecated It shouldn't be possible to fetch options without an attribute code associated, to form a coherent
     * identifier.
     *
     * @param string[] $attributeOptionCodes
     *
     * @return AttributeOption[]
     */
    public function findByCodes(array $attributeOptionCodes): array;
}

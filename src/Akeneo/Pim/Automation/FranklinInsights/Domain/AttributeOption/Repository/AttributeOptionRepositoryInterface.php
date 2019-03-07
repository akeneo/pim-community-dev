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

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
interface AttributeOptionRepositoryInterface
{
    /**
     * Find an attribute option by identifier.
     */
    public function findOneByIdentifier(string $identifier): ?AttributeOption;

    /**
     * Return an array of attribute option codes.
     *
     * @param string[] $attributeOptionCodes
     *
     * @return string[]
     */
    public function findCodesByIdentifiers(string $attributeCode, array $attributeOptionCodes): array;

    /**
     * Find attribute options by codes.
     *
     * @deprecated
     *
     * @param string[] $attributeOptionCodes
     *
     * @return AttributeOption[]
     */
    public function findByCode(array $attributeOptionCodes): array;
}

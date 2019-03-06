<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface AttributeRepositoryInterface
{
    public function findOneByIdentifier(string $attributeCode);

    public function findByCodes(array $codes);

    public function getAttributeTypeByCodes(array $attributeCodes): array;
}

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

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface AttributeRepositoryInterface
{
    /**
     * @param string $code
     *
     * @return Attribute|null
     */
    public function findOneByIdentifier(string $code): ?Attribute;

    /**
     * @param string[] $codes
     *
     * @return Attribute[]
     */
    public function findByCodes(array $codes): array;

    /**
     * @param string[] $codes
     *
     * @return array
     */
    public function getAttributeTypeByCodes(array $codes): array;
}

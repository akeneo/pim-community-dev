<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Repository interface for attribute
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface AttributeRepositoryInterface extends ObjectRepository
{
    /**
     * Get attribute type by code attributes
     *
     * @param array $codes
     *
     * @return array
     */
    public function getAttributeTypeByCodes(array $codes);
}

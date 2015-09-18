<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Product template repository interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductTemplateRepositoryInterface extends ObjectRepository
{
    /**
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    public function findByAttribute(AttributeInterface $attribute);
}

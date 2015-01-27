<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\UIBundle\Entity\Repository\OptionRepositoryInterface;

/**
 * Attribute option repository interface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeOptionRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    OptionRepositoryInterface,
    ObjectRepository
{
}

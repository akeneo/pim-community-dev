<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectsRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Attribute option repository interface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeOptionRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    ObjectRepository,
    IdentifiableObjectsRepositoryInterface
{
    /**
     * Return an array of attribute option codes
     *
     * @param string $code
     * @param array  $optionCodes
     *
     * @return array
     */
    public function findCodesByIdentifiers($code, array $optionCodes);
}

<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Helper\ResultParser;

/**
 * Group type repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupTypeRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * @return mixed
     */
    public function getAllGroupsExceptVariantQB();

    /**
     * @return mixed
     */
    public function createDatagridQueryBuilder();

    /**
     * @param string $code
     *
     * @return string
     */
    public function getTypeByGroup($code);

    /**
     * Find group type labels for a locale indexed by technical identifiers
     *
     * @param bool   $isVariant
     * @param string $locale
     *
     * @return array
     */
    public function findTypes($isVariant, $locale);

    /**
     * Find axis technical identifiers
     *
     * @param bool $isVariant
     *
     * @return array
     */
    public function findTypeIds($isVariant);
}

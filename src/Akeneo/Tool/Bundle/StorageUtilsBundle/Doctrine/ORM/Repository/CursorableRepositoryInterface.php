<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository;

/**
 * CursorableRepositoryInterface for ORM repositories
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CursorableRepositoryInterface
{
    /**
     * @param array $ids of the entities
     *
     * @return array
     */
    public function findByIds(array $ids);
}

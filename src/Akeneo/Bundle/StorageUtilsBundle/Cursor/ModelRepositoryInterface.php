<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Interface ModelRepositoryInterface
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ModelRepositoryInterface
{
    /**
     * @param array $ids of the entities
     * @return mixed : entities
     */
    public function findByIds(array $ids);
}

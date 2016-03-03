<?php

namespace Akeneo\Component\FileStorage\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * File repository interface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileInfoRepositoryInterface extends ObjectRepository, IdentifiableObjectRepositoryInterface
{
    /**
     * Finds a single object by a hash.
     *
     * @param string $hash
     *
     * @return object
     */
    public function findOneByHash($hash);
}

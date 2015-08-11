<?php

namespace Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository;

use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Implementation of FileRepositoryInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileRepository extends EntityRepository implements FileRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['key'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['key' => $identifier]);
    }
}

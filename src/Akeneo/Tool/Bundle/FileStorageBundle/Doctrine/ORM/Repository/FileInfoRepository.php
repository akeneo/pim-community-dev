<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Implementation of FileInfoRepositoryInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInfoRepository extends EntityRepository implements FileInfoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['id'];
    }

    public function findOneByIdentifier($identifier): ?FileInfo
    {
        if (!is_numeric($identifier)) {
            throw new \RuntimeException('temporary exception');
        }

        return $this->findOneBy(['id' => $identifier]);
    }
}

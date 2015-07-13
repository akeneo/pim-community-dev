<?php

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\Model\FileInterface;
use League\Flysystem\MountManager;

/**
 * Move a file from a virtual filesystem to another.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileMover implements FileMoverInterface
{
    /** @var MountManager */
    protected $mountManager;

    /**
     * @param MountManager $mountManager
     */
    public function __construct(MountManager $mountManager)
    {
        $this->mountManager = $mountManager;
    }

    /**
     * {@inheritdoc}
     */
    public function move(FileInterface $file, $srcFsAlias, $destFsAlias)
    {
        $isFileMoved = $this->mountManager->move(
            sprintf('%s://%s', $srcFsAlias, $file->getKey()),
            sprintf('%s://%s', $destFsAlias, $file->getKey())
        );

        if (!$isFileMoved) {
            throw new FileTransferException(
                sprintf(
                    'Impossible to move the file "%s" from "%s" to "%s".',
                    $file->getKey(),
                    $srcFsAlias,
                    $destFsAlias
                )
            );
        }

        $file->setStorage($destFsAlias);

        return $file;
    }
}

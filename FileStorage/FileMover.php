<?php

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;

/**
 * Move a file from a virtual filesystem to another
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileMover implements FileMoverInterface
{
    /** @var MountManager */
    protected $mountManager;

    /** @var SaverInterface */
    protected $saver;

    /**
     * @param MountManager   $mountManager
     * @param SaverInterface $saver
     */
    public function __construct(MountManager $mountManager, SaverInterface $saver)
    {
        $this->mountManager = $mountManager;
        $this->saver        = $saver;
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
        $this->saver->save($file);
    }
}

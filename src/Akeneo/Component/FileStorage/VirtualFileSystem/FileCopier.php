<?php

namespace Akeneo\Component\FileStorage\VirtualFileSystem;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\MountManager;

/**
 * Copy a file from a virtual filesystem to another.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileCopier implements FileCopierInterface
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
    public function copy($srcFsAlias, $srcKey, $dstFsAlias, $dstKey = null)
    {
        $dstKey = null !== $dstKey ? $dstKey : $srcKey;

        try {
            $isFileCopied = $this->mountManager->copy(
                sprintf('%s://%s', $srcFsAlias, $srcKey),
                sprintf('%s://%s', $dstFsAlias, $dstKey)
            );
        } catch (\Exception $e) {
            throw new FileTransferException(
                sprintf(
                    'Impossible to copy the file from "%s://%s" to "%s://%s".',
                    $srcFsAlias,
                    $srcKey,
                    $dstFsAlias,
                    $dstKey
                ),
                $e->getCode(),
                $e
            );
        }

        if (!$isFileCopied) {
            throw new FileTransferException(
                sprintf(
                    'Impossible to copy the file from "%s://%s" to "%s://%s".',
                    $srcFsAlias,
                    $srcKey,
                    $dstFsAlias,
                    $dstKey
                )
            );
        }
    }
}

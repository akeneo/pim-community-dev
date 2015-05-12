<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\FileHandler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGenerator;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use League\Flysystem\MountManager;

/**
 * Move a file from a source filesystem to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
abstract class AbstractFileHandler implements FileHandlerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var PathGenerator */
    protected $pathGenerator;

    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $srcFsAlias;

    /** @var string */
    protected $destFsAlias;

    /** @var string */
    protected $fileClass;

    /**
     * @param PathGenerator          $pathGenerator
     * @param MountManager           $mountManager
     * @param SaverInterface         $saver
     * @param string                 $srcFsAlias
     * @param string                 $destFsAlias
     * @param string                 $fileClass
     */
    public function __construct(
        PathGenerator $pathGenerator,
        MountManager $mountManager,
        SaverInterface $saver,
        $srcFsAlias,
        $destFsAlias,
        $fileClass = '\PimEnterprise\Component\ProductAsset\Model\File'
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->mountManager  = $mountManager;
        $this->saver         = $saver;
        $this->srcFsAlias    = $srcFsAlias;
        $this->destFsAlias   = $destFsAlias;
        $this->fileClass     = $fileClass;
    }

    /**
     * @return FileInterface
     */
    protected function createNewFile()
    {
        return new $this->fileClass();
    }
}

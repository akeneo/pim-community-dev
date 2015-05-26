<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use League\Flysystem\MountManager;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
abstract class AbstractRawFileStorer implements RawFileStorerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $fileClass;

    /**
     * @param PathGeneratorInterface $pathGenerator
     * @param MountManager           $mountManager
     * @param SaverInterface         $saver
     * @param string                 $fileClass
     */
    public function __construct(
        PathGeneratorInterface $pathGenerator,
        MountManager $mountManager,
        SaverInterface $saver,
        $fileClass = '\PimEnterprise\Component\ProductAsset\Model\File'
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->mountManager  = $mountManager;
        $this->saver         = $saver;
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

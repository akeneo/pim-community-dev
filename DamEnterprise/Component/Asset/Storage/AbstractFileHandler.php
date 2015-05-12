<?php

namespace DamEnterprise\Component\Asset\Storage;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use League\Flysystem\MountManager;

abstract class AbstractFileHandler implements FileHandlerInterface
{
    /** @var SaverInterface */
    protected $saver;

    /** @var PathGenerator */
    protected $pathGenerator;

    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $fileClass;

    public function __construct(
        PathGenerator $pathGenerator,
        MountManager $mountManager,
        $fileClass = '\DamEnterprise\Component\Asset\Model\File'
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->mountManager = $mountManager;
        $this->fileClass = $fileClass;
    }

    /**
     * @return FileInterface
     */
    protected function createNewFile()
    {
        return new $this->fileClass();
    }
}

<?php

namespace Pim\Bundle\EnrichBundle\Imagine\Loader;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Model\Binary;

/**
 * Image loader for Flysystem
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlysystemLoader implements LoaderInterface
{
    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $filesystemName;

    /**
     * @param MountManager $mountManager
     * @param string       $filesystemName
     */
    public function __construct(MountManager $mountManager, $filesystemName)
    {
        $this->mountManager   = $mountManager;
        $this->filesystemName = $filesystemName;
    }

    /**
     * {@inheritDoc}
     */
    public function find($path)
    {
        $filesystem = $this->getFilesystem();

        try {
            // TODO: Use another system to read files to prevent memory overflow
            $contents = $filesystem->read($path);
        } catch (FileNotFoundException $e) {
            // TODO: Return null of throw an exception?
        }

        $mimeType = $filesystem->getMimeType($path);

        if (false === $mimeType) {
            return $contents;
        }

        return new Binary($contents, $mimeType);
    }

    /**
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function getFilesystem()
    {
        return $this->mountManager->getFilesystem($this->filesystemName);
    }
}

<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Fetch files from the virtual storage filesystem to the local export filesystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileExporter implements FileExporterInterface
{
    /** @var RawFileFetcherInterface */
    protected $fileFetcher;

    /** @var MountManager */
    protected $mountManager;

    /** @var Filesystem */
    protected $localFs;

    /**
     * @param MountManager            $mountManager
     * @param RawFileFetcherInterface $fileFetcher
     */
    public function __construct(MountManager $mountManager, RawFileFetcherInterface $fileFetcher)
    {
        $this->mountManager = $mountManager;
        $this->fileFetcher  = $fileFetcher;
        $this->localFs      = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function export($key, $localPathname, $storageAlias)
    {
        if (!is_dir(dirname($localPathname))) {
            throw new \LogicException(sprintf('The export directory "%s" does not exist.', dirname($localPathname)));
        }

        $storageFs = $this->mountManager->getFilesystem($storageAlias);
        $rawFile = $this->fileFetcher->fetch($key, $storageFs);

        $copied = $this->copyFile($rawFile->getPathname(), $localPathname);
        //TODO: files should also be copied in the archive folder to be able to generate the ZIP file on the fly

        $this->localFs->remove($rawFile->getPathname());

        return $copied;
    }


    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    protected function copyFile($source, $destination)
    {
        $destinationDir = dirname($destination);

        try {
            if (!is_dir($destinationDir)) {
                $this->localFs->mkdir($destinationDir);
            }
            $this->localFs->copy($source, $destination, true);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }
}

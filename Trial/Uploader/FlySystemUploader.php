<?php

namespace Akeneo\Trial\Uploader;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\Sftp\SftpAdapter as SftpAdapter;

class FlySystemUploader implements UploaderInterface
{
    public function massUpload($directory, $type)
    {
        $directory = realpath($directory);
        if (false === $directory) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist', $directory));
        }

        $sourceFs = new Filesystem(new LocalAdapter($directory));
        $destFs   = $this->createDestinationFileSystem($type);

        $mountManager = new MountManager([
            'source' => $sourceFs,
            'dest'   => $destFs
        ]);

        // returns the list of files and directories with all info in an array
        foreach ($sourceFs->listContents('', true) as $file) {
            if ('dir' !== $file['type']) { // crappy
                // stream by default when using the mount manager \o/
                $mountManager->copy(
                    sprintf('source://%s', $file['path']),
                    sprintf('dest://flysystem/%s', $file['path'])
                );
            }
        }
    }

    /**
     * @param string $type
     *
     * @return Filesystem
     */
    private function createDestinationFileSystem($type)
    {
        if (UploaderInterface::TYPE_LOCAL === $type) {
            $fs = new Filesystem(new LocalAdapter(UploaderInterface::LOCAL_DESTINATION));
        } elseif (UploaderInterface::TYPE_SFTP === $type) {
            $fs = new Filesystem(
                new SftpAdapter([
                    'host' => UploaderInterface::SFTP_HOST,
                    'port' => UploaderInterface::SFTP_PORT,
                    'username' => UploaderInterface::SFTP_USERNAME,
                    'password' => UploaderInterface::SFTP_PASSWORD,
                    'root' => UploaderInterface::SFTP_DESTINATION,
                ])
            );
        } else {
            throw new \RuntimeException(sprintf('"%s" upload has not been implemented for FlySystemUploader', $type));
        }

        return $fs;
    }
}

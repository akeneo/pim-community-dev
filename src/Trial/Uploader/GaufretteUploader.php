<?php

namespace Trial\Uploader;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Adapter\Sftp as SftpAdapter;

class GaufretteUploader implements UploaderInterface
{
    public function massUpload($directory, $type)
    {
        $directory = realpath($directory);
        if (false === $directory) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist', $directory));
        }

        $sourceFs = new Filesystem(new LocalAdapter($directory));
        $destFs   = $this->createDestinationFileSystem($type);

        // returns the list of files names + the list of directories
        foreach ($sourceFs->keys() as $filePath) {
            if (!$sourceFs->getAdapter()->isDirectory($filePath)) {
                // returns the full content of the file, no stream :(
                $content = $sourceFs->read($filePath);
                $destFs->write('gaufrette/' . $filePath, $content);
            }
        }
    }

    public function uploadAlreadyExistent()
    {
        @touch(UploaderInterface::LOCAL_DESTINATION . 'already.there');

        $fs = $this->createDestinationFileSystem('local');

        return $fs->write('already.there', 'It is a test!');
    }

    public function downloadNonExistent()
    {
        $fs = $this->createDestinationFileSystem('local');

        return $fs->read('not.there');
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
            $sshConf = new \Ssh\Configuration(UploaderInterface::SFTP_HOST, UploaderInterface::SFTP_PORT);
            $sshSession = new \Ssh\Session($sshConf);
            $sshAuthent = new \Ssh\Authentication\Password(UploaderInterface::SFTP_USERNAME, UploaderInterface::SFTP_PASSWORD);
            $sftpClient = new \Ssh\Sftp($sshSession, $sshAuthent);
            $fs = new Filesystem(new SftpAdapter($sftpClient));
        } else {
            throw new \RuntimeException(sprintf('"%s" upload has not been implemented for FlySystemUploader', $type));
        }

        return $fs;
    }
}

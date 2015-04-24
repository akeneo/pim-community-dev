<?php

namespace Akeneo\Trial\Uploader;

interface UploaderInterface
{
    const TYPE_SFTP = 'sftp';
    const TYPE_LOCAL = 'local';

    const LOCAL_DESTINATION = '/tmp/upload_test/';

    const SFTP_HOST = 'fileserver.akeneo.com';
    const SFTP_USERNAME = 'upload';
    const SFTP_PASSWORD = 'Wi1Ohk4F';
    const SFTP_PORT = 2215;
    const SFTP_DESTINATION = 'jjanvier_test/';

    /**
     * Upload all files of the $directory with the chosen $type of upload (local, sftp...)
     *
     * @param string $directory
     * @param string $type
     */
    public function massUpload($directory, $type);
}

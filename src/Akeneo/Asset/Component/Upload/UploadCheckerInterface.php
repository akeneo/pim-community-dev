<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Upload;

use Akeneo\Asset\Component\Upload\Exception\UploadException;

/**
 * Check uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface UploadCheckerInterface
{
    /**
     * @param string $filename
     *
     * @return ParsedFilenameInterface
     */
    public function getParsedFilename($filename);

    /**
     * Validate a parsed filename
     *
     * @param ParsedFilenameInterface $parsedFilename
     *
     * @throws UploadException
     */
    public function validateFilenameFormat(ParsedFilenameInterface $parsedFilename);

    /**
     * Validate a filename before uploading or scheduling it
     *
     * @param ParsedFilenameInterface $parsedFilename
     * @param string                  $tmpUploadDir   Temporary directory for uploaded files
     * @param string                  $tmpImportDir Temporary directory for importd files
     *
     * @throws UploadException
     */
    public function validateUpload(ParsedFilenameInterface $parsedFilename, $tmpUploadDir, $tmpImportDir);
}

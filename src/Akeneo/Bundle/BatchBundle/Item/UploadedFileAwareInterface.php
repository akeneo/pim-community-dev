<?php

namespace Akeneo\Bundle\BatchBundle\Item;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface for reader with uploaded file
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @deprecated will be removed in 1.6, please use JobParameters uploadAllowed property + the Upload validation group
 */
interface UploadedFileAwareInterface
{
    /**
     * Get uploaded file constraints
     */
    public function getUploadedFileConstraints();

    /**
     * Set uploaded file
     *
     * @param File $uploadedFile
     */
    public function setUploadedFile(File $uploadedFile);
}

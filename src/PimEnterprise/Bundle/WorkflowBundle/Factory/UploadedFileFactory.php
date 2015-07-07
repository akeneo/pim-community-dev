<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Factory of uploaded file
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class UploadedFileFactory
{
    /**
     * Create a configured UploadedFile instance
     *
     * @param string  $path         The full temporary path to the file
     * @param string  $originalName The original file name
     * @param string  $mimeType     The type of the file as provided by PHP
     * @param int     $size         The file size
     * @param int     $error        The error constant of the upload (one of PHP's UPLOAD_ERR_XXX constants)
     * @param Boolean $test         Whether the test mode is active
     *
     * @return UploadedFile|null
     */
    public function create($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false)
    {
        try {
            return new UploadedFile($path, $originalName, $mimeType, $size, $error, $test);
        } catch (FileNotFoundException $e) {
            //TODO: use a real logger...
            error_log(sprintf('An error occured during the creation of the uploaded file: %s.', $e->getMessage()));
        }
    }
}

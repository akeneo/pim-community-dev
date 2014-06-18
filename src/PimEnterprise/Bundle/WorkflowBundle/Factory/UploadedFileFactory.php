<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Factory of uploaded file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class UploadedFileFactory
{
    /**
     * Create a configured UploadedFile instance
     *
     * @param string  $path         The full temporary path to the file
     * @param string  $originalName The original file name
     * @param string  $mimeType     The type of the file as provided by PHP
     * @param integer $size         The file size
     * @param integer $error        The error constant of the upload (one of PHP's UPLOAD_ERR_XXX constants)
     * @param Boolean $test         Whether the test mode is active
     *
     * @return UploadedFile
     */
    public function create($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false)
    {
        return new UploadedFile($path, $originalName, $mimeType, $size, $error, $test);
    }
}

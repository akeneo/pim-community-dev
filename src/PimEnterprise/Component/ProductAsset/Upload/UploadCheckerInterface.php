<?php
/**
 * Created by PhpStorm.
 * User: jml
 * Date: 8/24/15
 * Time: 3:01 PM
 */
namespace PimEnterprise\Component\ProductAsset\Upload;

/**
 * Manage upload of an asset file
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface UploadCheckerInterface
{
    /**
     * Get check result status
     *
     * @return string
     */
    public function getCheckStatus();

    /**
     * Validate a filename
     *
     * @param string $filename
     */
    public function isValidFilename($filename);
}

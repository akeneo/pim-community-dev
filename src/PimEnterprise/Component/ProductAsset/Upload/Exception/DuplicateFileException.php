<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload\Exception;

use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;

/**
 * Exception thrown when trying to upload a file already uploaded
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DuplicateFileException extends UploadException
{
    /** @var string */
    protected $message = UploadMessages::ERROR_CONFLICTS;
}

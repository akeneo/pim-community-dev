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
 * Exception thrown when trying to upload a file conflicting with another already uploaded file
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class UploadConflictException extends UploadException
{
    /** @var string */
    protected $message = UploadMessages::ERROR_CONFLICTS;
}

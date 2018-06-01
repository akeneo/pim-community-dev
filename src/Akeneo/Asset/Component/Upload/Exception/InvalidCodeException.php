<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Upload\Exception;

use Akeneo\Asset\Component\Upload\UploadMessages;

/**
 * Exception thrown when trying to upload a file with invalid asset code
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class InvalidCodeException extends UploadException
{
    /** @var string */
    protected $message = UploadMessages::ERROR_CODE;
}

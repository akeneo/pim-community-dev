<?php

namespace Pim\Bundle\BatchBundle\Item;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface for reader with uploaded file
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UploadedFileAwareInterface
{
    /**
     * Set uploaded file
     *
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile);
}

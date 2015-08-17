<?php

namespace Pim\Bundle\EnrichBundle\File;

/**
 * Filetype guesser interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FileTypeGuesserInterface
{
    /**
     * Return the type (defined in Pim\Bundle\EnrichBundle\File\FileTypes) corresponding to the specified MIME type
     *
     * @param string $mimeType
     *
     * @return string
     */
    public function guess($mimeType);
}

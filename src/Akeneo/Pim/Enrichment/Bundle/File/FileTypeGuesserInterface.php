<?php

namespace Akeneo\Pim\Enrichment\Bundle\File;

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
     * Return the type (defined in Akeneo\Pim\Enrichment\Bundle\File\FileTypes) corresponding to the specified MIME type
     *
     * @param string $mimeType
     *
     * @return string
     */
    public function guess($mimeType);
}

<?php

namespace Akeneo\Pim\Enrichment\Bundle\File;

/**
 * Provides default images by file type
 *
 * @see Pim\Bundle\EnrichBundle\File\FileTypes
 * @see Pim\Bundle\EnrichBundle\File\FileTypeGuesser
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface DefaultImageProviderInterface
{
    /**
     * Return the url of the default image corresponding to the specified file type
     *
     * @param string $fileType File type, defined in Akeneo\Pim\Enrichment\Bundle\File\FileTypes
     * @param string $filter   Transformation filter name
     *
     * @return string
     */
    public function getImageUrl($fileType, $filter);
}

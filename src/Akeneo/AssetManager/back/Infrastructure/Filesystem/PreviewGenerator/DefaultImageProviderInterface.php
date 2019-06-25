<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface DefaultImageProviderInterface
{
    /**
     * Return the mediaLink of the default image corresponding to the specified file type
     *
     * @param string $fileType File type, defined in Akeneo\Pim\Enrichment\Bundle\File\FileTypes
     * @param string $filter   Transformation filter name
     *
     * @return string
     */
    public function getImageMediaLink($fileType, $filter);
}

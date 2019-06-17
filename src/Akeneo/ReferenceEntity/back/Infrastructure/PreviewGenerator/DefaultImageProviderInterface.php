<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator;

/**
 * Todo: It needs to be moved in Tools/Platform instead of duplicate Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
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

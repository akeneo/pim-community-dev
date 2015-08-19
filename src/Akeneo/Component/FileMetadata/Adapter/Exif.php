<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata\Adapter;

/**
 * Adapter implementation for EXIF metadata.
 *
 * EXIF 2.3 specifications http://www.cipa.jp/std/documents/e/DC-008-2012_E.pdf
 * EXIF tags http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/EXIF.html
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Exif extends AbstractAdapter
{
    /**
     * @param array $mimeTypes
     */
    public function __construct(array $mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'exif';
    }

    /**
     * {@inheritdoc}
     */
    public function all(\SplFileInfo $file)
    {
        return exif_read_data($file->getPathname(), null, true);
    }
}

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
        /*
         * On some files, it can happen that EXIF data are corrupted.
         * When exif_read_data() read corrupted data, it raises a warning (Incorrect APP1 Exif Identifier Code)
         *
         * Ignoring errors of 'exif_read_data()' will simply skip corrupted data, and returns valid ones, or nothing if
         * there is no metadata.
         *
         * Unfortunately, it's really hard to detect if given file will have corrupted / bad EXIF metadata,
         * see https://stackoverflow.com/a/8864064/1230775
         */
        $exif = @exif_read_data($file->getPathname(), null, true);

        return $exif ? $exif : [];
    }
}

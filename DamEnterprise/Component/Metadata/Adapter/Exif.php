<?php

namespace DamEnterprise\Component\Metadata\Adapter;

/**
 * EXIF 2.3 specifications http://www.cipa.jp/std/documents/e/DC-008-2012_E.pdf
 * EXIF tags http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/EXIF.html
 */
class Exif extends AbstractAdapter
{
    public function __construct($mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function getName()
    {
        return 'exif';
    }

    public function all(\SplFileInfo $file)
    {
        return exif_read_data($file->getPathname(), null, true);
    }

    private function flattenExifBlock(array $data)
    {
        $res = [];

        foreach ($data as $key => $row) {
            if (is_array($row)) {
                $res = array_merge($res, $row);
            } else {
                $res[$key] = $row;
            }
        }

        return $res;
    }
}

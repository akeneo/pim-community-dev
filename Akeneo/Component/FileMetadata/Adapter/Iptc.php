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
 * Adapter implementation for IPTC metadata.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Iptc extends AbstractAdapter
{
    /** @staticvar string Metadata segment index for IPTC data */
    const IPTC_BLOCK_KEY = 'APP13';

    /** @var array */
    protected $iptcHeaders;

    /**
     * @param array $mimeTypes
     *
     * TODO: mimetypes normally supported here http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/IPTC.html
     * TODO: JPG, TIFF, PNG, MIFF, PS, PDF, PSD, XCF and DNG
     */
    public function __construct(array $mimeTypes = ['image/jpeg', 'image/tiff', 'image/png'])
    {
        $this->mimeTypes   = $mimeTypes;
        $this->iptcHeaders = $this->getDefaultIptcHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'iptc';
    }

    /**
     * {@inheritdoc}
     */
    public function all(\SplFileInfo $file)
    {
        getimagesize($file->getPathname(), $info);
        if (isset($info[self::IPTC_BLOCK_KEY])) {
            $iptc = iptcparse($info[self::IPTC_BLOCK_KEY]);

            if (false !== $iptc) {
                return $this->cleanIptcBlock($iptc);
            }
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultIptcHeaders()
    {
        // list comes from
        // http://www.iptc.org/std/Iptc4xmpCore/1.0/documentation/Iptc4xmpCore_1.0-doc-CpanelsUserGuide_13.pdf
        // 7.3 Appendix section 3, Mapping IPTC IIMv4 to IPTC Core

        //TODO: careful, not the same that http://www.iptc.org/std/IIM/4.1/specification/IIMV4.1.pdf
        //TODO: careful, not the same that http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/IPTC.html
        // https://www.iptc.org/std/photometadata/documentation/GenericGuidelines/
        //index.htm#!Documents/fieldreferencetable.htm

        //TODO: which labels should we use ? IPTC or Photoshop ?
        return [
            '2#005' => 'Title',
            '2#010' => 'Urgency',
            '2#015' => 'Category',
            '2#020' => 'Supplemental Categories',
            '2#025' => 'Keywords',
            '2#040' => 'Instructions',
            '2#055' => 'Date Created',
            '2#080' => 'Creator',
            '2#085' => 'Creator\'s Jobtitle',
            '2#090' => 'City',
            '2#095' => 'State/Province',
            '2#101' => 'Country',
            '2#103' => 'Job Identifier',
            '2#105' => 'Headline',
            '2#110' => 'Provider',
            '2#115' => 'Source',
            '2#116' => 'Copyright Notice',
            '2#120' => 'Caption/Description',
            '2#122' => 'Caption/Description Writer',
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function cleanIptcBlock(array $data)
    {
        $res = [];

        foreach ($data as $key => $row) {
            $field = isset($this->iptcHeaders[$key]) ? $this->iptcHeaders[$key] : $key;

            // Directly returns an array for Keywords
            $res[$field] = ('2#025' === $key) ? $row : $row[0];
        }

        return $res;
    }
}

<?php

namespace DamEnterprise\Component\Metadata\Adapter;

class Iptc extends AbstractAdapter
{
    const IPTC_BLOCK_KEY = 'APP13';

    /** @var array */
    protected $iptcHeaders;

    /**
     * @param array $mimeTypes
     *
     * TODO: mimetypes normally supported here http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/IPTC.html
     * TODO: JPG, TIFF, PNG, MIFF, PS, PDF, PSD, XCF and DNG
     */
    public function __construct($mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes   = $mimeTypes;
        $this->iptcHeaders = $this->getDefaultIptcHeaders();
    }

    public function getName()
    {
        return 'iptc';
    }

    public function all(\SplFileInfo $file)
    {
        getimagesize($file->getPathname(), $info);
        if (isset($info[self::IPTC_BLOCK_KEY])) {
            $iptc = iptcparse($info[self::IPTC_BLOCK_KEY]);

            return $this->cleanIptcBlock($iptc);
        }

        return [];
    }

    protected function getDefaultIptcHeaders()
    {
        // list comes from
        // http://www.iptc.org/std/Iptc4xmpCore/1.0/documentation/Iptc4xmpCore_1.0-doc-CpanelsUserGuide_13.pdf
        // 7.3 Appendix section 3, Mapping IPTC IIMv4 to IPTC Core

        //TODO: careful, not the same that http://www.iptc.org/std/IIM/4.1/specification/IIMV4.1.pdf
        //TODO: careful, not the same that http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/IPTC.html

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

    protected function cleanIptcBlock(array $data)
    {
        $res = [];

        foreach ($data as $key => $row) {
            $field       = isset($this->iptcHeaders[$key]) ? $this->iptcHeaders[$key] : $key;
            $res[$field] = $row[0];
        }

        return $res;
    }
}

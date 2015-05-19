<?php

namespace spec\Akeneo\Component\FileMetadata;

use PhpSpec\ObjectBehavior;

class FileMetadataBagSpec extends ObjectBehavior
{
    function let()
    {
        $originalData = [
            'exif' => [
                'COMPUTED' => [
                    'FocalPlaneYResolution' => 320,
                    'FocalPlaneXResolution' => 680,
                    'Thumbnail.Author'      => 'Jack',
                    'Thumbnail.Software'    => 'Gimp'
                ],
                'IFD0' => [
                    'WhiteBalance' => 'Yes'
                ]
            ],
            'iptc' => []
        ];

        $this->beConstructedWith($originalData);
    }

    function it_is_a_file_metadata_bag()
    {
        $this->shouldHaveType('\Akeneo\Component\FileMetadata\FileMetadataBagInterface');
    }

    function it_adds_and_gets_all_data()
    {
        $this->all()->shouldReturn([
            'exif' => [
                'COMPUTED' => [
                    'FocalPlaneYResolution' => 320,
                    'FocalPlaneXResolution' => 680,
                    'Thumbnail.Author'      => 'Jack',
                    'Thumbnail.Software'    => 'Gimp'
                ],
                'IFD0' => [
                    'WhiteBalance' => 'Yes'
                ]
            ],
            'iptc' => []
        ]);

        $moreData = [
            'exif' => [
                'COMPUTED' => [
                    'IsColor' => true
                ]
            ]
        ];

        $this->add($moreData);
        $this->all()->shouldReturn([
            'exif' => [
                'COMPUTED' => [
                    'FocalPlaneYResolution' => 320,
                    'FocalPlaneXResolution' => 680,
                    'Thumbnail.Author'      => 'Jack',
                    'Thumbnail.Software'    => 'Gimp',
                    'IsColor'               => true
                ],
                'IFD0' => [
                    'WhiteBalance' => 'Yes'
                ]
            ],
            'iptc' => []
        ]);
    }

    function it_gets_data()
    {
        $this->get('exif.COMPUTED.FocalPlaneXResolution')->shouldReturn(680);
        $this->get('exif.COMPUTED.Thumbnail\.Author')->shouldReturn('Jack');
        $this->get('exif.ifd0.whitebalance')->shouldReturn('Yes');
        $this->get('exif.IFD0.IsColor')->shouldReturn(null);
        $this->get('exif.IFD0.IsColor', true)->shouldReturn(true);
        $this->get('exif.COMPUTED')->shouldReturn([
            'FocalPlaneYResolution' => 320,
            'FocalPlaneXResolution' => 680,
            'Thumbnail.Author'      => 'Jack',
            'Thumbnail.Software'    => 'Gimp'
        ]);
    }

    function it_returns_the_presence_of_data()
    {
        $this->has('exif.IFD0.whitebalance')->shouldReturn(true);
        $this->has('exif.IFD0.BlackBalance')->shouldReturn(false);
        $this->has('exif.COMPUTED')->shouldReturn(true);
    }
}

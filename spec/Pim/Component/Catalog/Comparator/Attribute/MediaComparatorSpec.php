<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class MediaComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_file', 'pim_catalog_file']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_media_type()
    {
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_gets_changes_when_add_file()
    {
        $file = new \SplFileInfo(__FILE__);
        $changes   = ['data' => ['filePath' => $file->getPath()]];
        $originals = ['data' => ['filePath' => null]];

        $return = $changes;
        $return['data']['filename'] = 'Attribute';
        $this->compare($changes, $originals)->shouldReturn($return);
    }

    function it_does_not_change_not_updated_file()
    {
        $file = new \SplFileInfo(__FILE__);
        $changes   = ['data' => ['filePath' => $file->getPath()]];
        $originals = ['data' => ['filePath' => $file->getPath()]];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}

<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use PhpSpec\ObjectBehavior;

class MediaComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_media_type()
    {
        $this->supports('pim_catalog_file')->shouldReturn(true);
        $this->supports('pim_catalog_image')->shouldReturn(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_gets_changes_when_add_file()
    {
        $file = new \SplFileInfo(__FILE__);
        $changes   = ['value' => ['filePath' => $file->getPath()]];
        $originals = ['value' => ['filePath' => null]];

        $return = $changes;
        $return['value']['filename'] = 'Attribute';
        $this->compare($changes, $originals)->shouldReturn($return);
    }

    function it_does_not_change_not_updated_file()
    {
        $file = new \SplFileInfo(__FILE__);
        $changes   = ['value' => ['filePath' => $file->getPath()]];
        $originals = ['value' => ['filePath' => $file->getPath()]];

        $this->compare($changes, $originals)->shouldReturn(null);
    }
}

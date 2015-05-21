<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class MediaComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\AttributeComparatorInterface');
    }

    function it_supports_media_type()
    {
        $this->supportsComparison('pim_catalog_file')->shouldReturn(true);
        $this->supportsComparison('pim_catalog_image')->shouldReturn(true);
        $this->supportsComparison('other')->shouldBe(false);
    }

    function it_get_changes_when_add_file()
    {
        $changes   = ['value' => ['filePath' => '/tmp/foo.jpg']];
        $originals = ['value' => ['filePath' => null]];

        $return = $changes;
        $return['value']['filename'] = 'foo.jpg';
        $this->getChanges($changes, $originals)->shouldReturn($return);
    }

    function it_does_not_change_not_updated_file()
    {
        $changes   = ['value' => ['filePath' => '/tmp/foo.jpg']];
        $originals = ['value' => ['filePath' => '/tmp/foo.jpg']];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}

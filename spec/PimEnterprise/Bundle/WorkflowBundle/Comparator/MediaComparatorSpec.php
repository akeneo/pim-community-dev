<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MediaComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_get_changes_when_add_file(
        ProductValueInterface $value
    ) {
        $submittedData = [
            'id' => 1,
            'media' => [
                'filename' => 'foo.jpg',
                'originalFilename' => 'foo',
                'filePath' => '/tmp/foo.jpg',
                'mimeType' => 'image/jpeg',
                'size' => 1001,
            ],
        ];

        $this->getChanges($value, $submittedData)->shouldReturn($submittedData);
    }

    function it_get_changes_when_removing_file(
        ProductValueInterface $value
    ) {
        $submittedData = [
            'id' => 1,
            'media' => [
                'removed' => true,
            ],
        ];
        $this->getChanges($value, $submittedData)->shouldReturn($submittedData);
    }

    function it_does_get_changes_when_no_info_is_available(
        ProductValueInterface $value
    ) {
        $submittedData = [
            'id' => 1,
            'media' => [],
        ];
        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}

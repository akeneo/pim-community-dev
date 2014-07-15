<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class MediaComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_get_changes_when_uploading_file(
        $mediaManager,
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 1,
            'media' => [
                'removed' => true,
            ],
        ];
        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => 1,
            'media' => [
                'removed' => true,
            ],
        ]);
    }
}

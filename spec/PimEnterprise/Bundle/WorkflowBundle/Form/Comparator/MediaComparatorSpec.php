<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class MediaComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_get_changes_when_uploading_file(
        $mediaManager,
        UploadedFile $file,
        AbstractProductValue $value
    ) {
        throw new \PhpSpec\Exception\Example\SkippingException('UploadedFile unusable with Prophecy for now');

        $submittedData = [
            'id' => 1,
            'media' => [
                'file' => $file,
                'removed' => true,
            ],
        ];
        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => 1,
            'media' => [
                'file' => $file,
                'removed' => true,
            ],
        ]);
    }
}

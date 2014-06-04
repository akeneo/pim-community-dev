<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class MediaComparatorSpec extends ObjectBehavior
{
    function let(MediaManager $mediaManager)
    {
        $this->beConstructedWith($mediaManager);
    }

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

        $file->getClientSize()->willReturn(42);
        $mediaManager->handle(
            Argument::allOf(
                Argument::type('Pim\Bundle\CatalogBundle\Model\Media'),
                Argument::which('getFile', $file->getWrappedObject())
            ),
            Argument::containingString('proposal-')
        )->will(function(array $args) {
            $args[0]->setOriginalFilename('foo.jpg');
            $args[0]->setFilePath('/tmp/foo.jpg');
            $args[0]->setMimeType('image/jpg');
        });

        $submittedData = [
            'id' => 1,
            'media' => ['file' => $file],
        ];
        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => 1,
            'media' => [
                'originalFilename' => 'foo.jpg',
                'filePath' => '/tmp/foo.jpg',
                'mimeType' => 'image/jpg',
                'size' => 42,
            ]
        ]);
    }

    function it_does_not_detect_changes_when_no_file_is_uploaded(
        AbstractProductValue $value
    ) {
        $this->getChanges($value, ['media' => null])->shouldReturn(null);
    }

    function it_does_not_detect_changes_if_no_uploaded_file_is_present_and_current_value_is_empty(
        AbstractProductValue $value
    ) {
        $this->getChanges($value, ['media' => ['file' => '']])->shouldReturn(null);
    }
}

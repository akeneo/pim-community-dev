<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Box\Spout\Common\Type;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Xlsx reader
 */
class XlsxReader extends Reader implements ItemReaderInterface, UploadedFileAwareInterface, StepExecutionAwareInterface
{
    protected $type = Type::XLSX;

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return [
            new Assert\NotBlank(),
            new AssertFile(
                [
                    'allowedExtensions' => ['xlsx', 'zip']
                ]
            )
        ];
    }
}

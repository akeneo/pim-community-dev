<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplierRegistry;
use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformer
{
    private OperationApplierRegistry $operationApplierRegistry;

    public function __construct(OperationApplierRegistry $operationApplierRegistry)
    {
        $this->operationApplierRegistry = $operationApplierRegistry;
    }

    public function transform(File $sourceFile, Transformation $transformation): File
    {
        foreach ($transformation->getOperationCollection() as $operation) {
            $applier = $this->operationApplierRegistry->getApplier($operation);
            try {
                $sourceFile = $applier->apply($sourceFile, $operation);
            } catch (ExceptionInterface | RuntimeException $e) {
                throw new TransformationException($e->getMessage(), $e->getCode(), $e);
            }
        }
        // clear PHP's internal cache for the file's metadata (filesize, etc...).
        clearstatcache();

        return $this->rename($sourceFile, $transformation);
    }

    private function rename(File $sourceFile, Transformation $transformation): File
    {
        $extension = ('' === $sourceFile->getExtension()) ? '' : '.' . $sourceFile->getExtension();
        $newFilename = sprintf(
            '%s%s%s.%s',
            $transformation->getFilenamePrefix() ?? '',
            $sourceFile->getBasename($extension),
            $transformation->getFilenameSuffix() ?? '',
            $transformation->getOperationCollection()->hasOperation('optimize_jpeg') ? 'jpeg' : 'png'
        );

        return $sourceFile->move($sourceFile->getPath(), $newFilename);
    }
}

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
use Liip\ImagineBundle\Exception\ExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformer
{
    /** @var OperationApplierRegistry */
    private $operationApplierRegistry;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(OperationApplierRegistry $operationApplierRegistry, Filesystem $filesystem)
    {
        $this->operationApplierRegistry = $operationApplierRegistry;
        $this->filesystem = $filesystem;
    }

    public function transform(File $sourceFile, Transformation $transformation): File
    {
        $file = $this->rename($sourceFile, $transformation);
        foreach ($transformation->getOperationCollection() as $operation) {
            $applier = $this->operationApplierRegistry->getApplier($operation);
            try {
                $file = $applier->apply($file, $operation);
            } catch (ExceptionInterface $e) {
                throw new TransformationException($e->getMessage(), $e->getCode(), $e);
            }
        }
        // clear PHP's internal cache for the file's metadata (filesize, etc...).
        clearstatcache();

        return $file;
    }

    private function rename(File $sourceFile, Transformation $transformation): File
    {
        $extension = ('' === $sourceFile->getExtension()) ? '' : '.' . $sourceFile->getExtension();
        $newFilename = sprintf(
            '%s%s%s%s%s',
            '' === $sourceFile->getPath() ? '' : ($sourceFile->getPath() . DIRECTORY_SEPARATOR),
            $transformation->getFilenamePrefix() ?? '',
            $sourceFile->getBasename($extension),
            $transformation->getFilenameSuffix() ?? '',
            $extension
        );
        $this->filesystem->copy($sourceFile->getPathname(), $newFilename);

        return new File($newFilename, false);
    }
}

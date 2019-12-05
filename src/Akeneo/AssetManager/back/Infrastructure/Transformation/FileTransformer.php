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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplierRegistry;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformer
{
    /** @var OperationApplierRegistry */
    private $operationApplierRegistry;

    public function __construct(OperationApplierRegistry $operationApplierRegistry)
    {
        $this->operationApplierRegistry = $operationApplierRegistry;
    }

    public function transform(File $file, OperationCollection $operations): File
    {
        foreach ($operations as $operation) {
            $applier = $this->operationApplierRegistry->getApplier($operation);
            $file = $applier->apply($file, $operation);
        }
        // clear PHP's internal cache for the file's metadata (filesize, etc...).
        clearstatcache();

        return $file;
    }
}

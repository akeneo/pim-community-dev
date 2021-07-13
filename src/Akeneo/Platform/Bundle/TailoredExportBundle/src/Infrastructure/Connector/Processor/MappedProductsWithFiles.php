<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Platform\TailoredExport\Domain\FileToExport;

class MappedProductsWithFiles
{
    private array $mappedProducts;

    /** @var FileToExport[] */
    private array $filesToExport;

    public function __construct(array $mappedProducts, array $filesToExport)
    {
        $this->mappedProducts = $mappedProducts;
        $this->filesToExport = $filesToExport;
    }

    public function getMappedProducts(): array
    {
        return $this->mappedProducts;
    }

    public function getFilesToExport(): array
    {
        return $this->filesToExport;
    }
}

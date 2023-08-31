<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ImageFile;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DeleteCategoryImageFile
{
    public function __invoke(string $filePath): void;
}

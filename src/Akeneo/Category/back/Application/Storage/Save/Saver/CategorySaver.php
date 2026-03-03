<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategorySaver
{
    /**
     * @return string[]
     */
    public function getSupportedUserIntents(): array;

    public function save(Category $category): void;
}

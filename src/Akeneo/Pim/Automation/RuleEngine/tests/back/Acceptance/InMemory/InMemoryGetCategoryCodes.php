<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryGetCategoryCodes implements GetGrantedCategoryCodes
{
    /** @var InMemoryCategoryRepository */
    private $categoryRepository;

    public function __construct(InMemoryCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function forGroupIds(array $groupIds): array
    {
        $categoryCodes = [];

        /** @var CategoryInterface $category */
        foreach ($this->categoryRepository->findAll() as $category) {
            $categoryCodes[] = $category->getCode();
        }

        return $categoryCodes;
    }
}

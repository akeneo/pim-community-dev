<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Reader\Doctrine;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Connector\Reader\Doctrine\BaseReader;

/**
 * Get asset categories
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetCategoryReader extends BaseReader
{
    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepository;

    /**
     * @param CategoryRepositoryInterface $assetCategoryRepository
     */
    public function __construct(CategoryRepositoryInterface $assetCategoryRepository)
    {
        parent::__construct($assetCategoryRepository);

        $this->assetCategoryRepository = $assetCategoryRepository;
    }

    /**
     * @return \ArrayIterator
     */
    protected function getResults()
    {
        return new \ArrayIterator($this->assetCategoryRepository->getOrderedAndSortedByTreeCategories());
    }
}

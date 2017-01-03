<?php

namespace Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;

/**
 * Category reader that reads categories ordered by tree and order inside the tree
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends AbstractReader implements
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /**
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResults()
    {
        return new \ArrayIterator($this->repository->getOrderedAndSortedByTreeCategories());
    }
}

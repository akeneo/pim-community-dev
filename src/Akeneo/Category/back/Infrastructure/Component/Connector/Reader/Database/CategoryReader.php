<?php

namespace Akeneo\Category\Infrastructure\Component\Connector\Reader\Database;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader;

/**
 * Category reader that reads categories ordered by tree and order inside the tree.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends AbstractReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    protected function getResults()
    {
        return new \ArrayIterator($this->repository->getOrderedAndSortedByTreeCategories());
    }
}

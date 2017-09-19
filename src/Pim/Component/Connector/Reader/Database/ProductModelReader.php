<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * The product model repository reader
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelReader extends AbstractReader implements
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{
    /** @var ProductModelRepositoryInterface */
    protected $repository;

    /**
     * @param ProductModelRepositoryInterface $repository
     */
    public function __construct(ProductModelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResults()
    {
        // TODO PIM-6737: temporary until we discuss how to fetch all models, needs a new elastic search index
        return new \ArrayIterator($this->repository->findAll());
    }
}

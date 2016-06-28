<?php

namespace Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Base reader
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseReader extends AbstractReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var ObjectRepository */
    protected $repository;

    /**
     * @param ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \ArrayIterator
     */
    protected function getResults()
    {
        return new \ArrayIterator($this->repository->findAll());
    }
}

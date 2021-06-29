<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User extends AbstractReader implements
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{
    protected ObjectRepository $repository;

    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResults(): \ArrayIterator
    {
        return new \ArrayIterator(
            $this->repository->findBy(['type' => \Akeneo\UserManagement\Component\Model\User::TYPE_USER])
        );
    }
}

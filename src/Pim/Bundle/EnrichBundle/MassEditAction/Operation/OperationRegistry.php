<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Registry of mass edit actions indexed by gridName alias
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OperationRegistry implements OperationRegistryInterface
{
    /** @var MassEditOperationInterface[] */
    protected $operations = [];

    /** @var MassEditOperationInterface[] */
    protected $gridOperations = [];

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If the operation is already registered
     */
    public function register(MassEditOperationInterface $operation, $operationAlias, $acl = null, $gridName = null)
    {
        if (isset($this->operations[$operationAlias])) {
            throw new \InvalidArgumentException(
                sprintf('An operation with the alias "%s" is already registered', $operationAlias)
            );
        }

        if (null !== $gridName) {
            if (!isset($this->gridOperations[$gridName])) {
                $this->gridOperations[$gridName] = [];
            }

            $this->gridOperations[$gridName][$operationAlias] = $operation;
        }

        $this->operations[$operationAlias] = $operation;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If the operation is not registered
     */
    public function get($operationAlias)
    {
        if (!isset($this->operations[$operationAlias])) {
            throw new \InvalidArgumentException(
                sprintf('No operation is registered with alias "%s"', $operationAlias)
            );
        }

        return $this->operations[$operationAlias];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If no operation is registered for the given datagrid name
     */
    public function getAllByGridName($gridName)
    {
        if (!isset($this->gridOperations[$gridName])) {
            throw new \InvalidArgumentException(
                sprintf('No operation is registered for datagrid "%s"', $gridName)
            );
        }

        return $this->gridOperations[$gridName];
    }
}

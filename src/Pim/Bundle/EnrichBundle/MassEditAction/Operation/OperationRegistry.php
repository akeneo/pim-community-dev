<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * OperationRegistry constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param SecurityFacade        $securityFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If the operation is already registered
     */
    public function register(
        MassEditOperationInterface $operation,
        $operationAlias,
        $gridName,
        $operationGroup,
        $acl = null
    ) {
        if (isset($this->operations[$operationAlias])) {
            throw new \InvalidArgumentException(
                sprintf('An operation with the alias "%s" is already registered', $operationAlias)
            );
        }

        if (null !== $acl && null !== $this->tokenStorage->getToken() && !$this->securityFacade->isGranted($acl)) {
            return;
        }

        $this->gridOperations[$gridName][$operationGroup][$operationAlias] = $operation;
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
     * @throws \InvalidArgumentException If no operation is registered for the given datagrid name and group
     */
    public function getAllByGridNameAndGroup($gridName, $operationGroup)
    {
        if (!isset($this->gridOperations[$gridName][$operationGroup])) {
            throw new \InvalidArgumentException(
                sprintf('No operation is registered for datagrid "%s" and group "%s"', $gridName, $operationGroup)
            );
        }

        return $this->gridOperations[$gridName][$operationGroup];
    }
}

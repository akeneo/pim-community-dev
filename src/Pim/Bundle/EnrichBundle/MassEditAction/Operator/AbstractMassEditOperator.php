<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;

/**
 * A batch operation operator
 * Applies batch operations to products passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditOperator implements MassEditOperatorInterface
{
    /** @var MassEditOperationInterface $operation */
    protected $operation;

    /** @var string $operationAlias */
    protected $operationAlias;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var string[] $acls The default acls for each configured operation, indexed by code */
    protected $acls = [];

    /** @var MassEditOperationInterface[] $operations The defined operations, indexed by code*/
    protected $operations = [];

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function registerMassEditAction($alias, MassEditOperationInterface $operation, $acl = null)
    {
        if (array_key_exists($alias, $this->operations)) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is already registered', $alias));
        }
        $this->operations[$alias] = $operation;
        if ($acl) {
            $this->acls[$alias] = $acl;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationChoices()
    {
        $choices = [];

        foreach (array_keys($this->operations) as $alias) {
            if ($this->isGranted($alias)) {
                $choices[$alias] = sprintf('pim_enrich.mass_edit_action.%s.label', $alias);
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * {@inheritdoc}
     */
    public function setOperationAlias($operationAlias)
    {
        if (!$this->isGranted($operationAlias)) {
            throw new \InvalidArgumentException(sprintf('Operation %s is not allowed', $operationAlias));
        }
        $this->operationAlias = $operationAlias;

        if (!isset($this->operations[$operationAlias])) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is not registered', $operationAlias));
        }

        $this->operation = $this->operations[$operationAlias];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return $this->operationAlias;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getName();

    /**
     * Returns true if the operation is allowed for the current user
     *
     * @param string $operationAlias
     *
     * @return bool
     */
    protected function isGranted($operationAlias)
    {
        return !isset($this->acls[$operationAlias]) ||
            $this->securityFacade->isGranted($this->acls[$operationAlias]);
    }
}

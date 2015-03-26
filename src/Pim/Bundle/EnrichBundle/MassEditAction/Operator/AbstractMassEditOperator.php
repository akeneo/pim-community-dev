<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use JMS\Serializer\Annotation\Exclude;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;

/**
 * A batch operation operator
 * Applies batch operations to products passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Exclude
 */
abstract class AbstractMassEditOperator
{
    /**
     * @var MassEditOperationInterface $operation
     * @Exclude
     */
    protected $operation;

    /**
     * @var string $operationAlias
     */
    protected $operationAlias;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * The defined operations, indexed by code
     *
     * @var MassEditOperationInterface[] $operations
     * @Exclude
     */

    /**
     * The default acls for each configured operation, indexed by code
     *
     * @var string[] $acls
     */
    protected $acls = [];

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * Register a batch operation into the operator
     *
     * @param string                     $alias
     * @param MassEditOperationInterface $operation
     * @param string                     $acl
     *
     * @throws \InvalidArgumentException
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
     * Get the operation choices to present in the batch operator form
     *
     * TODO: Move to registry
     *
     * @return array
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
     * Get the batch operation
     *
     * @return MassEditOperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set the batch operation alias
     * (Also set the batch operation if the alias is registered
     *
     * @param string $operationAlias
     *
     * @throws \InvalidArgumentException when the alias is not registered
     *
     * @return AbstractMassEditOperator
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
     * Get the operation alias
     *
     * @return string
     */
    public function getOperationAlias()
    {
        return $this->operationAlias;
    }

    /**
     * Returns the name of the operator
     * Used in the view to generate translation key
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Returns true if the operation is allowed for the current user
     *
     * @param string $operationAlias
     *
     * @return boolean
     */
    protected function isGranted($operationAlias)
    {
        return !isset($this->acls[$operationAlias]) ||
            $this->securityFacade->isGranted($this->acls[$operationAlias]);
    }
}

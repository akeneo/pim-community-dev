<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;

/**
 * A batch operation operator
 * Applies batch operations to products passed in the form of QueryBuilder
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassEditOperatorInterface
{
    /**
     * Register a batch operation into the operator
     *
     * @param string                     $alias
     * @param MassEditOperationInterface $operation
     * @param string                     $acl
     *
     * @throws \InvalidArgumentException
     */
    public function registerMassEditAction($alias, MassEditOperationInterface $operation, $acl = null);

    /**
     * Get the operation choices to present in the batch operator form
     *
     * TODO: Move to registry
     *
     * @return array
     */
    public function getOperationChoices();

    /**
     * Get the batch operation
     *
     * @return MassEditOperationInterface
     */
    public function getOperation();

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
    public function setOperationAlias($operationAlias);

    /**
     * Get the operation alias
     *
     * @return string
     */
    public function getOperationAlias();

    /**
     * Returns the name of the operator
     * Used in the view to generate translation key
     *
     * @return string
     */
    public function getName();
}

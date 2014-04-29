<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator;

/**
 * Registry of mass edit action operators indexed by gridName alias
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OperatorRegistry
{
    /** @var array */
    protected $operators = [];

    /** @var array */
    protected $names = [];

    /**
     * Registers an operator inside a gridName index
     *
     * @param string                 $gridName
     * @param MassEditActionOperator $operator
     */
    public function register($gridName, AbstractMassEditOperator $operator)
    {
        if (isset($this->operators[$gridName])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'An operator with the alias "%s" is already registered',
                    $gridName
                )
            );
        }

        if (in_array($operator->getName(), $this->names)) {
            throw new \LogicException(
                sprintf(
                    'An operator with the name "%s" is already registered',
                    $operator->getName()
                )
            );
        }

        $this->operators[$gridName] = $operator;
        $this->names[] = $operator->getName();
    }

    /**
     * Get the operator concerning a gridName
     *
     * @param string $gridName
     *
     * @return MassEditActionOperator
     */
    public function getOperator($gridName)
    {
        if (!isset($this->operators[$gridName])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No operator is registered for datagrid "%s"',
                    $gridName
                )
            );
        }

        return $this->operators[$gridName];
    }
}

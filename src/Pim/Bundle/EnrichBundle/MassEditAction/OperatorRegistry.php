<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditActionOperator;

/**
 * Registry of mass edit action operators indexed by gridName alias
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OperatorRegistry
{
    /** array $operators */
    protected $operators = [];

    /**
     * Registers an operator inside a gridName index
     *
     * @param string                 $gridName
     * @param MassEditActionOperator $operator
     */
    public function register($gridName, AbstractMassEditActionOperator $operator)
    {
        $this->operators[$gridName] = $operator;
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

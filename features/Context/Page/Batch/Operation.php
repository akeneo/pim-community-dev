<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;

/**
 * BatchOperation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Operation extends Wizard
{
    protected $path = '/enrich/batch-operation/choose?{products}';

    private $aliases = array(
        'Change status (Enable/Disable)' => 'change-status'
    );

    public function chooseOperation($operation)
    {
        $value = $this->getAlias($operation);
        $this->selectFieldOption('pim_catalog_batch_operation[operationAlias]', $value);

        return $this;
    }

    private function getAlias($operation)
    {
        if (!array_key_exists($operation, $this->aliases)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown operation "%s" (available: "%s")',
                    $operation,
                    join('", "', array_keys($this->aliases))
                )
            );
        }

        return $this->aliases[$operation];
    }
}

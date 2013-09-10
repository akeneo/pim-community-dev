<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Driver\BrowserKitDriver;

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

    private $steps = array(
        'Change status (Enable/Disable)' => 'Batch ChangeStatus',
        'Edit attributes'                => 'Batch EditCommonAttributes',
    );

    public function chooseOperation($operation)
    {
        $choice = $this->findField($operation);

        if (null === $choice) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'form field',
                'id|name|label|value',
                $operation
            );
        }

        $driver = $this->getSession()->getDriver();
        if ($driver instanceof BrowserKitDriver) {
            $this->selectFieldOption('pim_catalog_batch_operation[operationAlias]', $choice->getAttribute('value'));
        } else {
            $driver->click($choice->getXpath());
        }

        $this->currentStep = $this->getStep($operation);

        return $this;
    }

    private function getStep($operation)
    {
        if (!array_key_exists($operation, $this->steps)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown operation "%s" (available: "%s")',
                    $operation,
                    join('", "', array_keys($this->aliases))
                )
            );
        }

        return $this->steps[$operation];
    }
}

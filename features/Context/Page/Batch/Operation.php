<?php

namespace Context\Page\Batch;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Wizard;

/**
 * BatchOperation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Operation extends Wizard
{
    protected $steps = array(
        'Change status'                    => 'Batch ChangeStatus',
        'Edit common attributes'           => 'Batch EditCommonAttributes',
        'Modifier les attributs communs'   => 'Batch EditCommonAttributes',
        'Change family'                    => 'Batch ChangeFamily',
        'Add to groups'                    => 'Batch AddToGroups',
        'Add to a variant group'           => 'Batch AddToVariantGroup',
        'Set attributes requirements'      => 'Batch SetAttributeRequirements',
        'Add to categories'                => 'Batch Classify',
        'Move between categories'          => 'Batch Classify',
        'Remove from categories'           => 'Batch Classify'
    );

    /**
     * @param string $operation
     *
     * @throws ElementNotFoundException
     *
     * @return Operation
     */
    public function chooseOperation($operation)
    {
        $choice = $this->spin(function () use ($operation) {
            $choices = $this->findAll('css', '.operation');
            foreach ($choices as $choice) {
                if (trim($choice->getText()) === $operation) {
                    return $choice;
                }
            }

            return null;
        }, sprintf('Cannot find operation "%s"', $operation));

        $choice->click();

        $this->currentStep = $this->getStep($operation);

        return $this;
    }

    /**
     * @param string $operation
     * @param string $page
     *
     * @return Operation
     */
    public function addStep($operation, $page)
    {
        $this->steps[$operation] = $page;

        return $this;
    }

    /**
     * @param string $operation
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getStep($operation)
    {
        if (!array_key_exists($operation, $this->steps)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown operation "%s" (available: "%s"). Please add it to Context\Page\Batch\Operation.',
                    $operation,
                    implode('", "', array_keys($this->steps))
                )
            );
        }

        return $this->steps[$operation];
    }
}

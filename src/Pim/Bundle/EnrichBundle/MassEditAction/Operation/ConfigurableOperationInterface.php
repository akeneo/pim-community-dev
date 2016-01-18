<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfigurableOperationInterface
{
    /**
     * Get the form type to use in order to configure the operation
     *
     * @return string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getFormType();

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions();

    /**
     * Get the name of items this operation applies to
     *
     * @return string
     */
    public function getItemsName();

    /**
     * Initialize the operation, allowing to retrieve entities or whatever useful
     * to configure this operation
     */
    public function initialize();

    /**
     * Finalize the operation configuration, apply actions right before being
     * called by the batch job cli
     */
    public function finalize();
}

<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Symfony\Component\Form\FormTypeInterface;

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
     * @return string|FormTypeInterface
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
}

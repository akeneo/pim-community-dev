<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

/**
 * View updater interface, in order to update the form view
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ViewUpdaterInterface
{
    /**
     * Update the given form view
     *
     * @param array $view
     */
    public function update(array $view);
}

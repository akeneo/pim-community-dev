<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

/**
 * View updater interface, in order to update the form view
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ViewUpdaterInterface
{
    /**
     * Update the form view
     *
     * @param array  $views
     * @param string $key
     * @param string $name
     */
    public function update($attributeView);
}

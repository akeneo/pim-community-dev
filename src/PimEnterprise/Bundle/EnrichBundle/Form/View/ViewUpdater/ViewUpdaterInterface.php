<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\View\ViewUpdater;

/**
 * TEST
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
    public function update(array $views, $key, $name);
}

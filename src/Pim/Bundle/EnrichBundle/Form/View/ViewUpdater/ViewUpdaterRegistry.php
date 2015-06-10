<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

/**
 * Registry of view updaters
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewUpdaterRegistry
{
    /** @var ViewUpdaterInterface[] */
    protected $updaters = [];

    /**
     * Register a view updater
     *
     * @param ViewUpdaterInterface $updater
     * @param int                  $position
     */
    public function registerUpdater(ViewUpdaterInterface $updater, $position)
    {
        if (!isset($this->updaters[$position])) {
            $this->updaters[$position] = $updater;
        } else {
            $this->registerUpdater($updater, ++$position);
        }
    }

    /**
     * Get the view updaters
     *
     * @return ViewUpdaterInterface[]
     */
    public function getUpdaters()
    {
        ksort($this->updaters);

        return $this->updaters;
    }
}

<?php

namespace Context\Page\Base;

/**
 * Wizard base page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Wizard extends Form
{
    protected $elements = array(
        'Available attributes form' => array('css' => '#pim_enrich_mass_edit_action_operation_displayedAttributes'),
    );

    protected $currentStep;

    /**
     * Go to the next step
     * @return string
     */
    public function next()
    {
        $this->pressButton('Next');

        return $this->currentStep;
    }

    /**
     * Press the confirm button
     * @return string
     */
    public function confirm()
    {
        $this->pressButton('Confirm');

        return $this->currentStep;
    }
}

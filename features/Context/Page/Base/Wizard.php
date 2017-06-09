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
    protected $currentStep;

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Available attributes form' => ['css' => '#pim_enrich_mass_edit_choose_action_operation_displayedAttributes'],
                'Next'                      => ['css' => '.AknButtonList .AknButton--apply'],
                'Confirm'                   => ['css' => '.AknButtonList .AknButton--apply'],
            ]
        );
    }

    /**
     * Go to the next step
     *
     * @return string
     */
    public function next()
    {
        $this->spin(function () {
            return $this->getElement('Next')->click();
        }, 'Cannot got to the next step');

        return $this->currentStep;
    }

    /**
     * Press the confirm button
     *
     * @return string
     */
    public function confirm()
    {
        $this->spin(function () {
            return $this->getElement('Confirm')->click();
        }, 'Cannot confirm the wizard');

        return $this->currentStep;
    }
}

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
                'Next'                      => ['css' => '.configuration .btn-primary'],
                'Confirm'                   => ['css' => '.confirmation .btn-primary'],
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
        $this->getElement('Next')->click();

        return $this->currentStep;
    }

    /**
     * Press the confirm button
     *
     * @return string
     */
    public function confirm()
    {
        $this->getElement('Confirm')->click();

        return $this->currentStep;
    }
}

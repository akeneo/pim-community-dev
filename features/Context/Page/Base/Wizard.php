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
                'Cancel'                    => ['css' => '.wizard-action[data-action-target="grid"]'],
                'Choose'                    => ['css' => '.wizard-action[data-action-target="choose"]'],
                'Configure'                 => ['css' => '.wizard-action[data-action-target="configure"]'],
                'Confirm'                   => ['css' => '.wizard-action[data-action-target="confirm"]'],
                'Validate'                  => ['css' => '.wizard-action[data-action-target="validate"]'],
            ]
        );
    }

    /**
     * Go to the grid
     *
     * @return string
     */
    public function cancel()
    {
        $this->spin(function () {
            $this->getElement('Cancel')->click();

            return true;
        }, 'Cannot got to the grid');

        return $this->currentStep;
    }

    /**
     * Go to the choose step
     *
     * @return string
     */
    public function select()
    {
        $this->spin(function () {
            $this->getElement('Choose')->click();

            return true;
        }, 'Cannot got to the choose step');

        return $this->currentStep;
    }

    /**
     * Go to the configuration step
     *
     * @return string
     */
    public function choose()
    {
        $this->spin(function () {
            $this->getElement('Configure')->click();

            return true;
        }, 'Cannot got to the configuration step');

        return $this->currentStep;
    }

    /**
     * Go to the next step
     *
     * @return string
     */
    public function configure()
    {
        $this->spin(function () {
            $this->getElement('Confirm')->click();

            return true;
        }, 'Cannot got to the confirm step');

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
            $this->getElement('Validate')->click();

            return true;
        }, 'Cannot confirm the wizard');

        return $this->currentStep;
    }
}

<?php

namespace Context\Page\Batch;

use Behat\Mink\Session;
use Context\Page\Base\ProductEditForm;
use Pim\Behat\Decorator\Grid\PaginationDecorator;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends ProductEditForm
{
    protected $currentStep;

    /**
     * @param Session $session
     * @param Factory $pageFactory
     * @param array   $parameters
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Cancel'                    => ['css' => '.wizard-action[data-action-target="grid"]'],
                'Choose'                    => ['css' => '.wizard-action[data-action-target="choose"]'],
                'Configure'                 => ['css' => '.wizard-action[data-action-target="configure"]'],
                'Confirm'                   => ['css' => '.wizard-action[data-action-target="confirm"]'],
                'Validate'                  => ['css' => '.wizard-action[data-action-target="validate"]'],
                'Available attributes form' => [
                    'css' => '#pim_enrich_mass_edit_choose_action_operation_displayedAttributes',
                ],
                'Grid toolbar'              => [
                    'css'        => '.AknGridToolbar',
                    'decorators' => [
                        PaginationDecorator::class,
                    ],
                ],
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

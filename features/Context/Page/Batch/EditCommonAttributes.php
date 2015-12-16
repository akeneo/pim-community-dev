<?php

namespace Context\Page\Batch;

use Behat\Mink\Session;
use Context\Page\Base\ProductEditForm;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;

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
     * @param Session              $session
     * @param PageFactoryInterface $pageFactory
     * @param array                $parameters
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Available attributes button'     => ['css' => 'button.pimmultiselect'],
                'Available attributes add button' => ['css' => '.pimmultiselect .ui-multiselect-footer a'],
                'Available attributes form'       => [
                    'css' => '#pim_enrich_mass_edit_choose_action_operation_displayedAttributes'
                ],
                'Locales dropdown' => ['css' => '#pim_enrich_mass_edit_choose_action_operation_locale'],
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
        $this->pressButton('Next');

        return $this->currentStep;
    }

    /**
     * Press the confirm button
     *
     * @return string
     */
    public function confirm()
    {
        $this->pressButton('Confirm');

        return $this->currentStep;
    }
}

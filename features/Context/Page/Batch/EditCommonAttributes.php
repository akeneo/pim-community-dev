<?php

namespace Context\Page\Batch;

use Context\Page\Base\ProductEditForm;

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
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Available attributes button'     => ['css' => 'button:contains("Select attributes")'],
                'Available attributes add button' => ['css' => '.pimmultiselect a:contains("Select")'],
                'Available attributes form'       => [
                    'css' => '#pim_enrich_mass_edit_choose_action_operation_displayedAttributes'
                ],
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

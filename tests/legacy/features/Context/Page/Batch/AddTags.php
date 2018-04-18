<?php

namespace Context\Page\Batch;

use Behat\Mink\Element\Element;
use Context\Page\Base\Form;

/**
 * Asset mass-edit "add tags" step page/
 *
 * @author    Damien Carcel <damien.carcel@gmail.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class AddTags extends Form
{
    /** @var string */
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
                'Tags'    => ['css' => '.add-tags-to-assets .select2-container-multi'],
                'Cancel'                    => ['css' => '.AknButton[data-action-target="grid"]'],
                'Choose'                    => ['css' => '.AknButton[data-action-target="choose"]'],
                'Configure'                 => ['css' => '.AknButton[data-action-target="configure"]'],
                'Confirm'                   => ['css' => '.AknButton[data-action-target="confirm"]'],
                'Validate'                  => ['css' => '.AknButton[data-action-target="validate"]'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fillField($field, $value, Element $element = null)
    {
        $label = $this->extractLabelElement($field, $element);

        $this->fillMultiSelect2Field($label, $value);
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

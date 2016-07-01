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
                'Next'    => ['css' => '.configuration .btn-primary'],
                'Confirm' => ['css' => '.confirmation .btn-primary'],
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

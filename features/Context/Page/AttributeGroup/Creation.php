<?php

namespace Context\Page\AttributeGroup;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Form;

/**
 * Attribute group creation page
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '#/configuration/attribute-group/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Attribute list'              => ['css' => '.table.attributes'],
                'Available attributes button' => ['css' => '.add-attribute a.select2-choice'],
                'Available attributes list'   => ['css' => '.add-attribute .select2-results'],
                'Available attributes search' => ['css' => '.add-attribute .select2-search input[type="text"]'],
                'Available attributes add button' => ['css' => '.ui-multiselect-footer button'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $this->spin(function () {
            return $this->find('css', $this->elements['Available attributes button']['css']);
        }, sprintf('Cannot find element "%s"', $this->elements['Available attributes button']['css']));

        $list = $this->getElement('Available attributes list');
        if (!$list->isVisible()) {
            $this->openAvailableAttributesMenu();
        }

        sleep(1);
        $search = $this->getElement('Available attributes search');
        foreach ($attributes as $attributeLabel) {
            $search->setValue($attributeLabel);
            $this->spin(
                function () use ($list, $attributeLabel) {
                    $label = $list->find('css', sprintf('li span:contains("%s")', $attributeLabel));
                    if (null === $label) {
                        return false;
                    }

                    $label->click();

                    return true;
                },
                sprintf('Could not click on available attribute "%s".', $attributeLabel)
            );
        }

        return $this->getElement('Available attributes add button')->press();
    }

    /**
     * @param string $attribute
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($attribute)
    {
        return $this->spin(function () use ($attribute) {
            return $this->getElement('Attribute list')
                ->find('css', sprintf('tr:contains("%s") .remove-attribute', $attribute));
        }, sprintf('Cannot find delete link for %s', $attribute));
    }
}

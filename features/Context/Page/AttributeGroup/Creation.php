<?php

namespace Context\Page\AttributeGroup;

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
    protected $path = '/configuration/attribute-group/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Attributes' => array('css' => '.tab-pane.tab-attribute table'),
                'Attribute list' => ['css' => '#attributes-sortable'],
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

        $search = $this->getElement('Available attributes search');
        foreach ($attributes as $attributeLabel) {
            $search->setValue($attributeLabel);
            $label = $this->spin(
                function () use ($list, $attributeLabel) {
                    return $list->find('css', sprintf('li label:contains("%s")', $attributeLabel));
                },
                sprintf('Could not find available attribute "%s".', $attributeLabel)
            );

            $label->click();
        }

        $this->getElement('Available attributes add button')->press();
    }

    /**
     * @param string $attribute
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($attribute)
    {
        $attributeRow = $this->getElement('Attributes')->find('css', sprintf('tr:contains("%s")', $attribute));

        if (!$attributeRow) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute row "%s" in the attributes table',
                    $attribute
                )
            );
        }

        $removeLink = $attributeRow->find('css', 'a.remove-attribute');

        if (!$removeLink) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute remove link for "%s" in the attributes table',
                    $attribute
                )
            );
        }

        return $removeLink;
    }
}

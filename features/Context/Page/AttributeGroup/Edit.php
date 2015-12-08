<?php

namespace Context\Page\AttributeGroup;

/**
 * Attribute group edit page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    protected $path = '/configuration/attribute-group/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Attribute list' => array('css' => '#attributes-sortable'),
            )
        );
    }

    /**
     * @param string $attribute
     * @param int    $position
     *
     * @return Edit
     */
    public function dragAttributeToPosition($attribute, $position)
    {
        $selector             = $this->getElement('Attribute list')->getAttribute('id');
        $currentPosition      = $this->getAttributePosition($attribute);
        $manipulationFunction = 'insertAfter';
        $position--;

        if (0 === $position) {
            $manipulationFunction = 'insertBefore';
            $position++;
        }

        // Move the row
        $this->getDriver()->evaluateScript(sprintf(
            '$("#%s tr:eq(%d)").%s($("#%s tr:eq(%d)"));',
            $selector,
            $currentPosition - 1,
            $manipulationFunction,
            $selector,
            $position - 1
        ));

        // Trigger sortable update
        $this->getDriver()->evaluateScript(sprintf(
            '$("#%s").sortable("option").update();',
            $selector
        ));

        return $this;
    }

    /**
     * @param string $attribute
     *
     * @return int
     */
    public function getAttributePosition($attribute)
    {
        $rows = $this->getElement('Attribute list')->findAll('css', 'tr');

        foreach ($rows as $index => $row) {
            if ($row->find('css', sprintf(':contains("%s")', $attribute))) {
                return $index+1;
            }
        }

        throw new \InvalidArgumentException(sprintf('Attribute %s was not found', $attribute));
    }
}

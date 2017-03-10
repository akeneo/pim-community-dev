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
    protected $path = '#/configuration/attribute-group/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Attribute list' => ['css' => '#attributes-sortable'],
            ]
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
        $list = $this->getElement('Attribute list')->findAll('css', 'tr');
        $elt  = $this->getElement('Attribute list')->find('css', sprintf('tr:contains("%s") .handle', $attribute));

        if ($position > count($list)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to change the position to %d, only %s attributes present', $position, count($list))
            );
        }

        $this->dragElementTo($elt, $list[$position-1]);

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

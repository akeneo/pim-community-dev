<?php

namespace Context\Page\Group;

use Context\Page\Base\Form;

/**
 * Attribute creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    protected $path = '/enrich/attribute-group/edit/{id}';

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
     * @param string  $attribute
     * @param integer $position
     *
     * @return Edit
     */
    public function dragAttributeToPosition($attribute, $position)
    {
        $list = $this->getElement('Attribute list')->findAll('css', 'tr');
        $elt  = $this->getElement('Attribute list')->find('css', sprintf('tr:contains("%s")', $attribute));

        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Attribute %s was not found', $attribute));
        }

        if ($position > count($list)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to change the position to %d, only %s attributes present', $position, count($list))
            );
        }

        $eltHandle = $elt->find('css', '.handle');
        $target = $list[$position-1];

        $eltHandle->dragTo($target);

        return $this;
    }

    /**
     * @param string $attribute
     *
     * @return integer
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

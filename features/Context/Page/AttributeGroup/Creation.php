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
    protected $path = '#/configuration/attribute-group/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Attributes' => array('css' => '.tab-pane.tab-attribute table'),
            )
        );
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

<?php

namespace Context\Page\Export;

use Context\Page\Base\Form;

/**
 * Export edit page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '#/spread/export/{code}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Category tree' => [
                    'css'        => '.jstree',
                    'decorators' => ['Pim\Behat\Decorator\Tree\JsTreeDecorator']
                ],
                'Available attributes' => [
                    'css'        => '.add-attribute',
                    'decorators' => ['Pim\Behat\Decorator\Common\AddAttributeDecorator']
                ],
                'Attribute selector' => [
                    'css'        => '.AknFieldContainer.attributes',
                    'decorators' => ['Pim\Behat\Decorator\Export\Structure\AttributesDecorator']
                ]
            ],
            $this->elements
        );
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Used with the new 'add-attributes' module. The method should be in the Form parent
     * when legacy stuff is removed.
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $availableAttribute = $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');

        $availableAttribute->addAttributes($attributes);
    }
}

<?php

namespace Context\Page\VariantGroup;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form as Form;

/**
 * Variant group edit page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '/enrich/variant-group/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Main context selector' => [
                    'css'        => '#attribute-buttons',
                    'decorators' => [
                        'Pim\Behat\Decorator\ContextSwitcherDecorator'
                    ]
                ],
                'Add attributes button' => [
                    'css' => '.add-attribute',
                    'decorators' => [
                        'Pim\Behat\Decorator\Attribute\AttributeAdderDecorator'
                    ]
                ],
                'Attribute inputs' => [
                    'css' => '.tab-pane.product-values',
                    'decorators' => [
                        'Pim\Behat\Decorator\InputDecorator'
                    ]
                ],
                'Navbar buttons' => [
                    'css' => 'header .actions',
                    'decorators' => [
                        'Pim\Behat\Decorator\Navbar\ButtonDecorator'
                    ]
                ]
            ]
        );
    }

    /**
     * @param string $name
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findField($name)
    {
        return $this->getElement('Attribute inputs')->findField($name);
    }

    /**
     * Find field container
     *
     * @param string $label
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findFieldContainer($label)
    {
        $field = $this->findField($label);

        return $field->getParent();
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        return $this->find('css', sprintf('.control-group:contains("%s") .remove-attribute', $field));
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->getElement('Navbar buttons')->asynchronousSave('Save');
    }

    /**
     * @param string $name
     * @param string $scope
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findScopedField($name, $scope)
    {
        $label = $this->find('css', sprintf('label:contains("%s")', $name));
        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $name);
        }
        $scopeLabel = $label
            ->getParent()
            ->find('css', sprintf('label[title="%s"]', $scope));
        if (!$scopeLabel) {
            throw new ElementNotFoundException($this->getSession(), 'form label', 'title', $name);
        }

        return $this->find('css', sprintf('#%s', $scopeLabel->getAttribute('for')));
    }
}

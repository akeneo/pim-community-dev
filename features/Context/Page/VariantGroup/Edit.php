<?php

namespace Context\Page\VariantGroup;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form as Form;
use Context\Spin\TimeoutException;

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
                    'css'        => '.tab-container .product-attributes .attribute-edit-actions .context-selectors',
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
                'Attributes form' => [
                    'css' => '.tab-pane.product-values',
                    'decorators' => [
                        'Pim\Behat\Decorator\FormDecorator'
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
        try {
            return $this->getElement('Attributes form')->findAttribute($name);
        } catch (TimeoutException $e) {
            return $this->deprecatedFindField($name);
        } catch (ElementNotFoundException $e) {
            return $this->deprecatedFindField($name);
        }
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
     * {@inheritdoc}
     */
    public function fillField($field, $value, Element $element = null)
    {
        try {
            $this->getElement('Attributes form')->fillAttribute($field, $value, $element);
        } catch (TimeoutException $e) {
            parent::fillField($field, $value, $element);
        } catch (ElementNotFoundException $e) {
            parent::fillField($field, $value, $element);
        }
    }

    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function getRemoveLinkFor($field)
    {
        return $this->getElement('Attributes form')->getRemoveLinkFor($field);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->getElement('Navbar buttons')->clickButton('Save');

        $this->spin(function () {
            return null === $this->getSession()->getPage()->find(
                'css',
                '*:not(.hash-loading-mask):not(.grid-container):not(.loading-mask) > .loading-mask'
            );
        });
    }

    /**
     * @param string $inputLabel
     *
     * @return mixed
     */
    public function getInputValue($inputLabel)
    {
        return $this->getElement('Attributes form')->getInputValue($inputLabel);
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

    /**
     * Used for old variant group fields (not attribute related)
     *
     * @param string $name
     *
     * @return NodeElement|mixed|null
     *
     * @deprecated Please use $this->getElement('Attributes form')->findAttribute($name); if the field
     *             is inside an Attribute Form. This method is used for old forms.
     */
    protected function deprecatedFindField($name)
    {
        $label = $this->spin(function () use ($name) {
            return $this->find('css', sprintf('label:contains("%s")', $name));
        }, sprintf('Label "%s" not found', $name));

        $field = $this->spin(function () use ($label) {
            return $label->getParent()->find('css', 'input,textarea');
        }, sprintf('Form field with label "%s" not found', $name));

        return $field;
    }
}

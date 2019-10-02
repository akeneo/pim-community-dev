<?php

namespace Context\Page\Family;

use Context\Page\Base\Form;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\Common\AddSelect\AttributeGroupAddSelectDecorator;
use Pim\Behat\Decorator\Common\AttributeSelectorDecorator;

/**
 * Family edit page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    use SpinCapableTrait;

    /**
     * @var string
     */
    protected $path = '#/configuration/family/{code}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Attributes'                         => ['css' => '.tab-pane.tab-attribute table'],
                'Attribute as label choices'         => ['css' => '#pim_enrich_family_form_label_attribute_as_label'],
                'Available attributes button'        => ['css' => '.add-attribute a.select2-choice'],
                'Available attribute groups button'  => ['css' => '.add-attribute-group a.select2-choice'],
                'Available attributes list'          => ['css' => '.add-attribute .select2-results'],
                'Available attribute groups list'    => ['css' => '.add-attribute-group .select2-results'],
                'Available attributes search'        => ['css' => '.add-attribute .select2-search input[type="text"]'],
                'Available attribute groups search'  => ['css' => '.add-attribute-group .select2-search input[type="text"]'],
                'Available groups'                   => [
                    'css'        => '.add-attribute-group',
                    'decorators' => [AttributeGroupAddSelectDecorator::class],
                ],
                'Select2 dropmask'                   => ['css' => '.select2-drop-mask'],
                'Add family variant'                 => ['css' => '.add-variant'],
                'edit family variant attribute sets' => [
                    'css' => '.AknFamilyVariant.family-variant-levels',
                    'decorators' => [AttributeSelectorDecorator::class],
                ],
            ]
        );
    }

    /**
     * @param string $attributeName
     * @param string $groupName
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    public function getAttribute($attributeName, $groupName = null)
    {
        if (null !== $groupName) {
            return $this->getAttributeByGroupAndName($attributeName, $groupName);
        }

        return $this->getAttributeByName($attributeName);
    }

    /**
     * @param $attributeName
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    protected function getAttributeByName($attributeName)
    {
        $attributeNodes = $this->getElement('Attributes')->findAll('css', 'table.groups tbody tr:not(.group)');
        foreach ($attributeNodes as $attributeNode) {
            $attribute = $attributeNode->find('css', sprintf('td:contains("%s")', $attributeName));
            if (null !== $attribute) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @param $attribute
     * @param $group
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    protected function getAttributeByGroupAndName($attribute, $group)
    {
        $groupNode = $this->getElement('Attributes')->find('css', sprintf('tr.group:contains("%s")', $group));

        if (!$groupNode) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute group "%s" in the attributes table',
                    $group
                )
            );
        }

        return $groupNode->getParent()->find('css', sprintf('td:contains("%s")', $attribute));
    }

    /**
     * @param string $attribute
     *
     * @return \Behat\Mink\Element\NodeElement|mixed
     */
    public function getRemoveLinkFor($attribute)
    {
        return $this->spin(function () use ($attribute) {
            return $this->find(
                    'css',
                    sprintf('.remove-attribute[data-attribute="%s"]', $attribute)
                );
        }, sprintf('Remove link for field "%s" was not found', $attribute));
    }

    /**
     * @return array
     */
    public function getAttributeAsLabelOptions()
    {
        $options = $this->spin(function () {
            return $this->getElement('Attribute as label choices')->findAll('css', 'option');
        }, 'Cannot find attribute as label choices.');

        return array_map(
            function ($option) {
                return trim($option->getText());
            },
            $options
        );
    }

    /**
     * @param string $attributeCode
     * @param string $channelCode
     *
     * @return bool
     */
    public function isAttributeRequired($attributeCode, $channelCode)
    {
        $selector = '.attribute-requirement [data-channel="%s"][data-attribute="%s"]';
        $checkbox = $this->find('css', sprintf($selector, $channelCode, $attributeCode));
        if (!$checkbox) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find "%s" attribute requirement for channel "%s"',
                    $attributeCode,
                    $channelCode
                )
            );
        }

        return 'true' === $checkbox->getAttribute('data-required');
    }

    /**
     * @param string $attribute
     * @param string $channel
     */
    public function switchAttributeRequirement($attribute, $channel)
    {
        $this->spin(function () use ($attribute, $channel) {
            $cell = $this->getAttributeRequirementCell($attribute, $channel);
            if (null === $cell) {
                return false;
            }

            $requirement = $cell->find('css', 'i');
            if (null === $requirement) {
                return false;
            }

            $requirement->click();

            return true;
        }, sprintf('Impossible to switch attribute requirement "%s" for "%s"', $attribute, $channel));
    }

    /**
     * @param string $attribute
     * @param string $channel
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getAttributeRequirementCell($attribute, $channel)
    {
        return $this->spin(function () use ($attribute, $channel) {
            return $this->find('css', sprintf('i.AknAcl-icon[data-attribute="%s"][data-channel="%s"]', $attribute, $channel));
        }, sprintf('The cell for attribute "%s" and channel "%s" was not found', $attribute, $channel));
    }

    /**
     * Finds available attribute group
     *
     * @param string $group
     *
     * @return boolean
     */
    public function findAvailableAttributeGroup($group)
    {
        $addAttributeGroupElement = $this->spin(function () {
            return $this->getElement('Available groups');
        }, 'Can not find add by group option');

        return $addAttributeGroupElement->hasOption($group);
    }

    /**
     * Adds attributes by group
     *
     * @param array $groups
     */
    public function addAttributesByGroup($groups)
    {
        $addGroupElement = $this->spin(function () {
            return $this->getElement('Available groups');
        }, 'Can not find add by group select');

        $addGroupElement->addOptions($groups);
    }

    /**
     * Open the add family variant creation form
     */
    public function openFamilyVariantCreationForm()
    {
        $addFamilyVariantButton = $this->spin(function () {
            return $this->getElement('Add family variant');
        }, 'Can not find Add family variant button');

        $addFamilyVariantButton->click();
    }
}

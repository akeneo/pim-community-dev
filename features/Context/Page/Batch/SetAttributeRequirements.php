<?php

namespace Context\Page\Batch;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Wizard;
use Context\Spin\SpinCapableTrait;
use Context\Traits\ClosestTrait;
use Pim\Behat\Decorator\Common\AddAttributeDecorator;
use Pim\Behat\Decorator\Common\SelectGroupDecorator;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeRequirements extends Wizard
{
    use SpinCapableTrait;
    use ClosestTrait;

    protected $elements = [
        'Available attributes button'     => ['css' => 'button:contains("Select attributes")'],
        'Available attributes add button' => ['css' => '.pimmultiselect a:contains("Select")'],
        'Available attributes form'       => ['css' => '#pim_catalog_mass_edit_family_add_attribute'],
        'Attributes'                      => ['css' => 'table.groups'],
        'Available attributes'            => [
            'css'        => '.add-attribute',
            'decorators' => [AddAttributeDecorator::class]
        ],
        'Available groups'                  => [
            'css'        => '.add-attribute-group',
            'decorators' => [SelectGroupDecorator::class],
        ],
    ];

    /**
     * Switches attribute requirement
     *
     * @param string $attribute
     * @param string $channel
     */
    public function switchAttributeRequirement($attribute, $channel)
    {
        $cell        = $this->getAttributeRequirementCell($attribute, $channel);
        $requirement = $cell->find('css', 'i');

        $loadingMask = $this->find('css', '.hash-loading-mask .loading-mask');
        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, '".loading-mask" is still visible');

        $requirement->click();
    }

    /**
     * Gets attribute requirement cell
     *
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
            return $this->find('css', sprintf('.AknAcl-icon[data-attribute="%s"][data-channel="%s"]', $attribute, $channel));
        }, sprintf('The cell for attribute "%s" and channel "%s" was not found', $attribute, $channel));
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Used with the new 'add-attributes' module. The method should be in the Form parent
     * when legacy stuff is removed.
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $addAttributeDecorator = $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');

        $addAttributeDecorator->addAttributes($attributes);
    }

    /**
     * Adds attributes related to attribute groups selected
     *
     * @param string $groups
     */
    public function addAttributesByGroup($groups)
    {
        $addGroupElement = $this->spin(function () {
            return $this->getElement('Available groups');
        }, 'Can not find add by group select');

        $addGroupElement->addItems($groups);
    }

    /**
     * Gets attribute
     *
     * @param string $attributeName
     * @param string $groupName
     *
     * @return NodeElement|null
     */
    public function getAttribute($attributeName, $groupName = null)
    {
        if (null !== $groupName) {
            return $this->getAttributeByGroupAndName($attributeName, $groupName);
        }

        return $this->getAttributeByName($attributeName);
    }

    /**
     * Get attribute by group and name
     *
     * @param $attribute
     * @param $group
     *
     * @return NodeElement|null
     */
    protected function getAttributeByGroupAndName($attribute, $group)
    {
        $groupNode = $this->spin(function () use ($group) {
            return $this->getElement('Attributes')->find('css', sprintf('tr.group:contains("%s")', $group));
        }, sprintf('Couldn\'t find the attribute group "%s" in the attributes table', $group));

        return $this->getClosest($groupNode, 'group-wrapper')->find('css', sprintf('td:contains("%s")', $attribute));
    }

    /**
     * Gets attribute by name
     *
     * @param $attributeName
     *
     * @return NodeElement|null
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
}

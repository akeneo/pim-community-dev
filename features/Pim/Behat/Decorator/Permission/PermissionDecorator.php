<?php

namespace Pim\Behat\Decorator\Permission;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Rights decorator to be able navigate and find elements in the Role page
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PermissionDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Group'           => '.tab-groups .tab:contains("%s")',
        'Resource'        => '.acl-permission:contains("%s")',
        'Resource Toggle' => '.acl-permission-toggle.granted, .acl-permission-toggle.non-granted'
    ];

    /**
     * @param string $group
     *
     * @return NodeElement
     */
    public function findGroup($group)
    {
        return $this->spin(function () use ($group) {
            return $this->find('css', sprintf($this->selectors['Group'], $group));
        }, sprintf('Group "%s" not found.', $group));
    }

    /**
     * @param string $resource
     *
     * @return NodeElement
     */
    public function findResource($resource)
    {
        $resourceElement = $this->spin(function () use ($resource) {
            return $this->find('css', sprintf($this->selectors['Resource'], $resource));
        }, sprintf('Resource with label "%s" not found.', $resource));

        return $this->spin(function () use ($resourceElement) {
            return $resourceElement->find('css', $this->selectors['Resource Toggle']);
        }, sprintf('Resource with label "%s" found but the toggle was not found.', $resource));
    }

    /**
     * @param string $group
     */
    public function navigateToGroup($group)
    {
        $this->findGroup($group)->click();
    }

    /**
     * @param string $resource
     */
    public function grantResource($resource)
    {
        $resourceElement = $this->findResource($resource);

        if ($resourceElement->hasClass('non-granted')) {
            $this->toggleResource($resourceElement);
        }
    }

    /**
     * @param string $resource
     */
    public function revokeResource($resource)
    {
        $resourceElement = $this->findResource($resource);

        if ($resourceElement->hasClass('granted')) {
            $this->toggleResource($resourceElement);
        }
    }

    /**
     * @param string $group
     */
    public function grantGroup($group)
    {
        $iconElement = $this->findGroupIcon($group);

        if ($iconElement->hasClass('icon-remove') || $iconElement->hasClass('icon-circle')) {
            $iconElement->click();
        }
    }

    /**
     * @param string $group
     */
    public function revokeGroup($group)
    {
        $iconElement = $this->findGroupIcon($group);

        if ($iconElement->hasClass('icon-ok')) {
            $iconElement->click();
        }
    }

    /**
     * @param NodeElement $resource
     */
    public function toggleResource(NodeElement $resource)
    {
        $groupTitleElement = $this->spin(function () use ($resource) {
            return $resource->getParent()->getParent()->getParent()->find('css', 'h3');
        }, 'Group title not found for resource.');
        $groupTitle = $groupTitleElement->getHtml();

        $this->navigateToGroup($groupTitle);
        $resource->click();
    }

    /**
     * @param $group
     *
     * @return NodeElement
     */
    protected function findGroupIcon($group)
    {
        $groupElement = $this->findGroup($group);

        return $this->spin(function () use ($groupElement) {
            return $groupElement->find('css', '.acl-group-permission-toggle');
        }, sprintf('Group icon "%s" not found', $group));
    }
}

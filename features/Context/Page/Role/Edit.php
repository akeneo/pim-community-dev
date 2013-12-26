<?php

namespace Context\Page\Role;

use Context\Page\Base\Form;

/**
 * User role edit page
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
    protected $path = '/user/role/update/{id}';

    /**
     * Get ACL resource
     *
     * @param string $resource
     *
     * @return NodeElement
     * @throws \InvalidArgumentException
     */
    public function getResource($resource)
    {
        $element = $this->getElement('Container')->find('css', sprintf('strong:contains("%s")', $resource));

        if (!$element) {
            throw new \InvalidArgumentException(sprintf('Resource "%s" not found', $resource));
        }

        return $element->getParent()->getParent();
    }

    /**
     * Click a ACL resource link to load the list of choices
     *
     * @param string $resource
     */
    public function clickResourceField($resource)
    {
        $this->getResource($resource)->find('css', '.access_level_value_link a')->click();
    }

    /**
     * Set ACL resource rights
     *
     * @param string $resource
     * @param string $rights
     */
    public function setResourceRights($resource, $rights)
    {
        $this->getResource($resource)->find('css', 'select')->selectOption($rights);
    }
}

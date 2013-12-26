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
     * Get the resource right
     *
     * @param string $resource
     *
     * @return NodeElement
     * @throws \InvalidArgumentException
     */
    public function getResourceRight($resource)
    {
        $element = $this->getElement('Container')->find('css', sprintf('div.security-row:contains("%s")', $resource));

        if (!$element) {
            throw new \InvalidArgumentException(sprintf('Resource "%s" not found', $resource));
        }

        return $element;
    }

    /**
     * Get resource right field
     *
     * @param string $resource
     *
     * @return NodeElement
     */
    public function getResourceRightField($resource)
    {
        $element = $this->getResourceRight($resource);

        return $element->find('css', 'div.access_level_value a');
    }
}

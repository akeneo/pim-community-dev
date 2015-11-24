<?php

namespace Context\Page\UserRole;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Form;

/**
 * User role edit page
 *
 * @author    Jean-Marie Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '/user/role/update/{id}';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'ACL Group' => ['css' => '#rights-action ul li'],
                'ACL Role'  => ['css' => '#rights-action .acl-permission'],
            ],
            $this->elements
        );
    }

    /**
     * Select the specified $group
     *
     * @param string $group
     */
    public function selectGroup($group)
    {
        $element = $this->spin(function () use ($group) {
            return $this->getElement('ACL Group')->find('css', sprintf('a:contains("%s")', $group));
        }, 5, sprintf('Unable to find the ACL group', $group));

        $element->click();
    }

    /**
     * Select the specified $role
     *
     * @param string $role
     *
     * @throws ElementNotFoundException
     */
    public function selectRole($role)
    {
        $node = $this->spin(function () use ($role) {
            return $this->getElement('ACL Role')->find('css', sprintf('strong:contains("%s")', $role));
        }, 5, sprintf('Unable to find the ACL role', $role));

        $element = $node->getParent()->getParent()->find('css', 'i');
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'form field', 'label', $role);
        }

        $element->click();
    }
}

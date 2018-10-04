<?php

namespace Akeneo\UserManagement\Bundle\ViewElement\Checker;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker;

/**
 * Displays the groups and roles tab in the user form if groups or roles are defined
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsAndRolesVisibilityChecker extends NonEmptyPropertyVisibilityChecker
{
    /**
     * {@inheritdoc}
     */
    public function isVisible(array $config = [], array $context = [])
    {
        return parent::isVisible(['property' => '[form][groups]'], $context) ||
            parent::isVisible(['property'    => '[form][rolesCollection]'], $context);
    }
}

<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType as OroPrivilegeCollectionType;

/**
 * Overriden PrivilegeCollectionType to remove unused ACLs
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrivilegeCollectionType extends OroPrivilegeCollectionType
{
}

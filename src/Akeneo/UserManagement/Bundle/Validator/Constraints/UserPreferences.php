<?php

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for user preferences
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferences extends Constraint
{
    /**
     * @var string Violation message for missing catalog locale
     */
    public $missingLocaleMsg = 'Catalog locale may not be empty.';

    /**
     * @var string Violation message for missing catalog scope
     */
    public $missingScopeMsg = 'Catalog scope may not be empty.';

    /**
     * @var string Violation message for missing default tree
     */
    public $missingTreeMsg = 'Default tree may not be empty.';

    /**
     * @var string Violation message for inactive catalog locale
     */
    public $inactiveLocaleMsg = 'This locale is not activated.';

    /**
     * @var string Violation message for invalid default tree
     */
    public $invalidTreeMsg = 'This category is not a tree.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

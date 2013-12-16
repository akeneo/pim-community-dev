<?php

namespace Pim\Bundle\UserBundle\Validator\Constraints;

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
    public $missingLocaleMessage = 'Catalog locale may not be empty.';

    /**
     * @var string Violation message for missing catalog scope
     */
    public $missingScopeMessage = 'Catalog scope may not be empty.';

    /**
     * @var string Violation message for missing default tree
     */
    public $missingTreeMessage = 'Default tree may not be empty.';

    /**
     * @var string Violation message for inactive catalog locale
     */
    public $inactiveLocaleMessage = 'This locale is not activated.';

    /**
     * @var string Violation message for invalid default tree
     */
    public $invalidTreeMessage = 'This category is not a tree.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

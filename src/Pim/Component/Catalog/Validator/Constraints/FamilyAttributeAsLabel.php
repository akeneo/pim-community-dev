<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Family attribute_as_label constraint
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class FamilyAttributeAsLabel extends Constraint
{
    /** @var string */
    public $messageAttribute = 'The attribute "%attribute%" does not belong to the family, thus it cannot be used as '.
        'an "attribute as label" for this family';

    /** @var string */
    public $messageAttributeType = 'Only text and identifier attribute types can be used as "attribute as label" '.
        'for this family';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_family_attribute_as_label_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

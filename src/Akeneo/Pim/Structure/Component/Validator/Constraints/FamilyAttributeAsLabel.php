<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

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
    public $messageAttribute = 'Property "attribute_as_label" must belong to the family';

    /** @var string */
    public $messageAttributeType = 'Property "attribute_as_label" only supports "pim_catalog_text" and '.
        '"pim_catalog_identifier" attribute types for the family';

    /** @var string */
    public $propertyPath = 'attribute_as_label';

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

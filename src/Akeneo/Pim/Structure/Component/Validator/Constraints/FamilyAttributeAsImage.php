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
class FamilyAttributeAsImage extends Constraint
{
    /** @var string */
    public $messageAttribute = 'Property "attribute_as_image" must belong to the family';

    /** @var string */
    public $messageAttributeType = 'Property "attribute_as_image" only supports %s '.
        'attribute type for the family';

    /** @var string */
    public $messageAttributeGlobal = 'Property "attribute_as_image" must not be scopable nor localizable '.
        'for this family';

    /** @var string */
    public $propertyPath = 'attribute_as_image';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_family_attribute_as_image_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

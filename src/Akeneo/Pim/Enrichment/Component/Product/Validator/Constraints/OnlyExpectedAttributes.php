<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OnlyExpectedAttributes extends Constraint
{
    public const ATTRIBUTE_UNEXPECTED = '91367a0c-75a6-43aa-9359-bb522f511229';
    public const ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY = 'e5558015-241a-4463-86a0-80db670577ec';

    private static $errorMessages = [
        self::ATTRIBUTE_UNEXPECTED => 'pim_catalog.constraint.can_have_family_variant_unexpected_attribute',
        self::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY => 'pim_catalog.constraint.attribute_does_not_belong_to_family'
    ];

    /** @var string */
    public $propertyPath = 'attribute';

    /**
     * Returns the message of the given error code.
     *
     * @throws InvalidArgumentException If the error code does not exist
     */
    public static function getErrorMessage(string $errorCode): string
    {
        if (!isset(static::$errorMessages[$errorCode])) {
            throw new \InvalidArgumentException(sprintf(
                'The error code "%s" does not exist for constraint of type "%s".',
                $errorCode,
                static::class
            ));
        }

        return static::$errorMessages[$errorCode];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_only_expected_attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

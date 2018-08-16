<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SameFamilyThanParent extends Constraint
{
    public const MESSAGE = 'pim_catalog.constraint.variant_product_invalid_family';

    /** @var string */
    public $propertyPath = 'family';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_family_same_family_than_parent';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

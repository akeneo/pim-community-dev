<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamily extends Constraint
{
    public const MESSAGE = 'pim_catalog.constraint.not_null_family';

    /** @var string */
    public $propertyPath = 'family';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_family_not_empty';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

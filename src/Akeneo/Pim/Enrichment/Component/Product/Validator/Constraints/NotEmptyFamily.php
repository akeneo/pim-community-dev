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
    const NOT_EMPTY_FAMILY = 'd3b19dba-d82a-11eb-b8bc-0242ac130003';

    /** @var string */
    public $message = 'The family cannot be "null" because your product with the %sku% identifier is a variant product.';

    /** @var string */
    public $propertyPath = 'family';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_family_not_empty';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}

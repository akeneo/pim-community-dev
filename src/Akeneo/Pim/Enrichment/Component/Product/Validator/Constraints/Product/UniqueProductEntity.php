<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Symfony\Component\Validator\Constraint;

/**
 * Check that another product does not have the same identifier
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueProductEntity extends Constraint
{
    public $message = 'The same identifier is already set on another product';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_unique_product_validator_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

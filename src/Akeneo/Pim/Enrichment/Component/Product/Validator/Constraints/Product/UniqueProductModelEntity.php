<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product;

use Symfony\Component\Validator\Constraint;

/**
 * Check that another product model does not have the same identifier
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueProductModelEntity extends Constraint
{
    public $message = 'The same identifier is already set on another product model';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_unique_product_model_validator_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

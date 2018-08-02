<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Validation\Constraints;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingFamily
{
    public $message = 'A group does not exist in the given list of groups.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'batch_api_existing_families';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

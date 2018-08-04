<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingChannelsInValues extends Constraint
{
    public $message = 'One of the channel does not exist.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'batch_api_existing_channels';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

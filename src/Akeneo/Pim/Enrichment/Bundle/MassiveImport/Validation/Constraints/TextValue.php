<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValue extends Constraint
{
    public $message = 'Text value is not valid.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'batch_api_validate_text_value';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

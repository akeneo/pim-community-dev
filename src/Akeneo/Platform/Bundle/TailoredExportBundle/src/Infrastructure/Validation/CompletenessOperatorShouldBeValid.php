<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessOperatorShouldBeValid extends Constraint
{
    public const NOT_SUPPORTED_OPERATOR = 'akeneo.tailored_export.validation.completeness.not_supported_operators';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'akeneo.tailored_export.validation.completeness_operator_should_be_valid';
    }
}
